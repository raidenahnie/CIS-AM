<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsenceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AbsenceRequestController extends Controller
{
    /**
     * Get absence requests (for user: their own, for admin: all or filtered)
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Admin can see all requests or filter by user
            if ($user->isAdmin()) {
                $query = AbsenceRequest::with(['user', 'admin']);
                
                // Filter by status if provided
                if ($request->has('status') && $request->status !== 'all') {
                    $query->where('status', $request->status);
                }
                
                // Filter by user if provided
                if ($request->has('user_id')) {
                    $query->where('user_id', $request->user_id);
                }
                
                $requests = $query->orderBy('created_at', 'desc')->get();
            } else {
                // Regular users only see their own requests
                $requests = AbsenceRequest::with(['admin'])
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
            
            return response()->json([
                'success' => true,
                'requests' => $requests
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching absence requests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch absence requests'
            ], 500);
        }
    }

    /**
     * Store a new absence request
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|min:10|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            // Check for overlapping requests
            $overlap = AbsenceRequest::where('user_id', $user->id)
                ->where('status', '!=', 'rejected')
                ->where(function($query) use ($request) {
                    $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                        ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                        ->orWhere(function($q) use ($request) {
                            $q->where('start_date', '<=', $request->start_date)
                              ->where('end_date', '>=', $request->end_date);
                        });
                })
                ->exists();

            if ($overlap) {
                return response()->json([
                    'success' => false,
                    'error' => 'You already have a pending or approved request for overlapping dates.'
                ], 422);
            }

            $absenceRequest = AbsenceRequest::create([
                'user_id' => $user->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'reason' => $request->reason,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Leave request submitted successfully. Waiting for admin approval.',
                'request' => $absenceRequest->load('user')
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating absence request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to submit leave request'
            ], 500);
        }
    }

    /**
     * Approve an absence request (admin only)
     */
    public function approve(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Only admins can approve requests.'
                ], 403);
            }

            $absenceRequest = AbsenceRequest::findOrFail($id);

            if (!$absenceRequest->isPending()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This request has already been reviewed.'
                ], 422);
            }

            $absenceRequest->update([
                'status' => 'approved',
                'admin_id' => $user->id,
                'admin_comment' => $request->input('comment', null),
                'reviewed_at' => now()
            ]);

            // Create attendance records for each workday in the absence period
            $this->createAbsenceAttendanceRecords($absenceRequest);

            return response()->json([
                'success' => true,
                'message' => 'Leave request approved successfully. Attendance records have been created.',
                'request' => $absenceRequest->load(['user', 'admin'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving absence request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to approve leave request'
            ], 500);
        }
    }

    /**
     * Create attendance records for approved absence request
     */
    private function createAbsenceAttendanceRecords(AbsenceRequest $absenceRequest)
    {
        $startDate = Carbon::parse($absenceRequest->start_date);
        $endDate = Carbon::parse($absenceRequest->end_date);
        $currentDate = $startDate->copy();

        // Get user's primary workplace or first assigned workplace
        $user = \App\Models\User::find($absenceRequest->user_id);
        $workplace = $user->primaryWorkplace();
        
        if (!$workplace) {
            // Get first assigned workplace if no primary
            $workplace = $user->workplaces()->first();
        }
        
        if (!$workplace) {
            Log::warning('Cannot create absence records: User has no assigned workplace', [
                'user_id' => $absenceRequest->user_id
            ]);
            return;
        }

        while ($currentDate->lte($endDate)) {
            // Only create records for workdays (Monday to Friday)
            if ($currentDate->isWeekday()) {
                $dateStr = $currentDate->format('Y-m-d');
                
                // Check if attendance record already exists for this date
                $existingAttendance = \App\Models\Attendance::where('user_id', $absenceRequest->user_id)
                    ->where('date', $dateStr)
                    ->first();

                if (!$existingAttendance) {
                    // Create absence attendance record
                    \App\Models\Attendance::create([
                        'user_id' => $absenceRequest->user_id,
                        'workplace_id' => $workplace->id,
                        'date' => $dateStr,
                        'status' => 'excused',
                        'notes' => 'Approved Leave: ' . $absenceRequest->reason . 
                                   ($absenceRequest->admin_comment ? ' | Admin: ' . $absenceRequest->admin_comment : ''),
                        'check_in_time' => null,
                        'check_out_time' => null,
                        'is_approved' => true,
                        'approved_by' => $absenceRequest->admin_id,
                        'approved_at' => $absenceRequest->reviewed_at,
                    ]);
                }
            }
            $currentDate->addDay();
        }
    }

    /**
     * Reject an absence request (admin only)
     */
    public function reject(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Only admins can reject requests.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'comment' => 'required|string|min:10|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $absenceRequest = AbsenceRequest::findOrFail($id);

            if (!$absenceRequest->isPending()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This request has already been reviewed.'
                ], 422);
            }

            $absenceRequest->update([
                'status' => 'rejected',
                'admin_id' => $user->id,
                'admin_comment' => $request->comment,
                'reviewed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Leave request rejected.',
                'request' => $absenceRequest->load(['user', 'admin'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error rejecting absence request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to reject leave request'
            ], 500);
        }
    }

    /**
     * Delete an absence request (only if pending and user's own)
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $absenceRequest = AbsenceRequest::findOrFail($id);

            // Only allow users to delete their own pending requests
            if ($absenceRequest->user_id !== $user->id && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized to delete this request.'
                ], 403);
            }

            if (!$absenceRequest->isPending()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete a request that has been reviewed.'
                ], 422);
            }

            $absenceRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Absence request deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting absence request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete absence request'
            ], 500);
        }
    }

    /**
     * Get pending requests count (for admin dashboard badge)
     */
    public function getPendingCount()
    {
        try {
            $count = AbsenceRequest::pending()->count();
            
            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pending count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch pending count'
            ], 500);
        }
    }
}
