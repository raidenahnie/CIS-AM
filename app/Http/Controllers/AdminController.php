<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Workplace;
use App\Models\UserWorkplace;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        $users = User::with('workplaces')->get();
        $workplaces = Workplace::withCount('users')->get();
        
        // Get latest attendance data for each user today
        $latestAttendance = [];
        foreach ($users as $user) {
            $latestLog = \App\Models\AttendanceLog::where('user_id', $user->id)
                                                 ->where('timestamp', '>=', now()->startOfDay())
                                                 ->orderBy('timestamp', 'desc')
                                                 ->with('workplace')
                                                 ->first();
            if ($latestLog) {
                $latestAttendance[$user->id] = [
                    'action' => $latestLog->action,
                    'address' => $latestLog->address ?: 'Location not available',
                    'workplace_name' => $latestLog->workplace ? $latestLog->workplace->name : 'Unknown',
                    'timestamp' => $latestLog->timestamp
                ];
            }
        }
        
        return view('admin.dashboard', compact('users', 'workplaces', 'latestAttendance'));
    }

    /**
     * Get all users for API
     */
    public function getUsers()
    {
        $users = User::select('id', 'name', 'email', 'role')->get();
        
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    /**
     * Get all workplaces for API
     */
    public function getWorkplaces()
    {
        $workplaces = Workplace::select('id', 'name', 'address', 'is_active')
                              ->where('is_active', true)
                              ->get();
        
        return response()->json([
            'success' => true,
            'workplaces' => $workplaces
        ]);
    }

    /**
     * Get workplace data for editing
     */
    public function getWorkplace(Workplace $workplace)
    {
        $workplace->loadCount('users');
        
        return response()->json([
            'success' => true,
            'workplace' => $workplace
        ]);
    }

    /**
     * Store a new user
     */
    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user
        ]);
    }

    /**
     * Get user data
     */
    public function getUser(User $user)
    {
        return response()->json([
            'success' => true,
            'user' => $user->load('workplaces')
        ]);
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,user'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        // Prevent deleting the last admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the last admin user'
            ], 422);
        }

        // Prevent users from deleting themselves
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account'
            ], 422);
        }

        // Remove all workplace assignments
        $user->workplaces()->detach();
        
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Manage user workplace assignments
     */
    public function getUserWorkplaces(User $user)
    {
        $userWorkplaces = $user->workplaces()->get();
        $availableWorkplaces = Workplace::whereNotIn('id', $userWorkplaces->pluck('id'))->get();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ],
            'userWorkplaces' => $userWorkplaces,
            'availableWorkplaces' => $availableWorkplaces
        ]);
    }

    /**
     * Set primary workplace for user
     */
    public function setPrimaryWorkplace(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'workplace_id' => 'required|exists:workplaces,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($request->user_id);

        // Check if user is assigned to this workplace
        $assignment = $user->workplaces()->where('workplace_id', $request->workplace_id)->first();
        
        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'User is not assigned to this workplace'
            ], 422);
        }

        // Remove primary status from all user's workplaces
        $user->workplaces()->updateExistingPivot($user->workplaces->pluck('id'), ['is_primary' => false]);

        // Set new primary workplace
        $user->workplaces()->updateExistingPivot($request->workplace_id, ['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Primary workplace updated successfully'
        ]);
    }

    /**
     * Get workplace users for management
     */
    public function getWorkplaceUsers(Workplace $workplace)
    {
        $workplaceUsers = $workplace->users()->get();
        $availableUsers = User::whereNotIn('id', $workplaceUsers->pluck('id'))->get();

        return response()->json([
            'success' => true,
            'workplace' => [
                'id' => $workplace->id,
                'name' => $workplace->name
            ],
            'workplaceUsers' => $workplaceUsers,
            'availableUsers' => $availableUsers
        ]);
    }

    /**
     * Update user workplace role
     */
    public function updateUserWorkplaceRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'workplace_id' => 'required|exists:workplaces,id',
            'role' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($request->user_id);
        $user->workplaces()->updateExistingPivot($request->workplace_id, ['role' => $request->role]);

        return response()->json([
            'success' => true,
            'message' => 'User role updated successfully'
        ]);
    }



    /**
     * Store a new workplace
     */
    public function storeWorkplace(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:1|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $workplace = Workplace::create([
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workplace created successfully',
            'workplace' => $workplace->load('users')
        ]);
    }

    /**
     * Update a workplace
     */
    public function updateWorkplace(Request $request, Workplace $workplace)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:1|max:1000',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $workplace->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Workplace updated successfully',
            'workplace' => $workplace->load('users')
        ]);
    }

    /**
     * Delete a workplace
     */
    public function deleteWorkplace(Workplace $workplace)
    {
        // Check if workplace has active assignments
        $hasActiveUsers = $workplace->users()->count() > 0;
        
        if ($hasActiveUsers) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete workplace with assigned users. Please remove all user assignments first.'
            ], 422);
        }

        $workplace->delete();

        return response()->json([
            'success' => true,
            'message' => 'Workplace deleted successfully'
        ]);
    }

    /**
     * Assign workplace to user
     */
    public function assignWorkplace(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'workplace_id' => 'required|exists:workplaces,id',
            'is_primary' => 'boolean',
            'role' => 'string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if assignment already exists
        $existingAssignment = UserWorkplace::where('user_id', $request->user_id)
                                          ->where('workplace_id', $request->workplace_id)
                                          ->first();

        if ($existingAssignment) {
            return response()->json([
                'success' => false,
                'message' => 'User is already assigned to this workplace'
            ], 422);
        }

        // If this is set as primary, remove primary status from other workplaces
        if ($request->is_primary) {
            UserWorkplace::where('user_id', $request->user_id)
                         ->update(['is_primary' => false]);
        }

        UserWorkplace::create([
            'user_id' => $request->user_id,
            'workplace_id' => $request->workplace_id,
            'role' => $request->role ?? 'employee',
            'is_primary' => $request->is_primary ?? false,
            'assigned_at' => now(),
            'effective_from' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User assigned to workplace successfully'
        ]);
    }

    /**
     * Remove workplace assignment
     */
    public function removeWorkplaceAssignment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'workplace_id' => 'required|exists:workplaces,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $assignment = UserWorkplace::where('user_id', $request->user_id)
                                  ->where('workplace_id', $request->workplace_id)
                                  ->first();

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found'
            ], 404);
        }

        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Workplace assignment removed successfully'
        ]);
    }

    /**
     * Get employee locations for mapping
     */
    public function getEmployeeLocations()
    {
        try {
            $employeeLocations = [];
            
            // Get all users and their latest attendance logs (same logic as dashboard)
            $users = User::all();
            
            foreach ($users as $user) {
                $latestLog = \App\Models\AttendanceLog::where('user_id', $user->id)
                                                     ->where('timestamp', '>=', now()->startOfDay())
                                                     ->orderBy('timestamp', 'desc')
                                                     ->with(['user', 'workplace'])
                                                     ->first();
                
                if ($latestLog && $latestLog->latitude && $latestLog->longitude) {
                    $employeeLocations[] = [
                        'user_id' => $latestLog->user_id,
                        'user_name' => $latestLog->user->name,
                        'action' => $latestLog->action,
                        'latitude' => (float) $latestLog->latitude,
                        'longitude' => (float) $latestLog->longitude,
                        'address' => $latestLog->address ?: null, // Keep original logic for address
                        'timestamp' => $latestLog->timestamp,
                        'workplace_name' => $latestLog->workplace ? $latestLog->workplace->name : null
                    ];
                }
            }

            // Get workplaces for boundaries
            $workplaces = Workplace::where('is_active', true)
                                  ->select('id', 'name', 'address', 'latitude', 'longitude', 'radius')
                                  ->get()
                                  ->map(function ($workplace) {
                                      return [
                                          'id' => $workplace->id,
                                          'name' => $workplace->name,
                                          'address' => $workplace->address,
                                          'latitude' => (float) $workplace->latitude,
                                          'longitude' => (float) $workplace->longitude,
                                          'radius' => (int) $workplace->radius
                                      ];
                                  });

            return response()->json([
                'success' => true,
                'employeeLocations' => $employeeLocations,
                'workplaces' => $workplaces
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading employee locations: ' . $e->getMessage(),
                'employeeLocations' => [],
                'workplaces' => []
            ], 500);
        }
    }

    /**
     * Get detailed location history for a specific user
     */
    public function getUserLocationDetails(User $user)
    {
        try {
            $locations = \App\Models\AttendanceLog::where('user_id', $user->id)
                                                 ->where('timestamp', '>=', now()->startOfDay())
                                                 ->with('workplace')
                                                 ->orderBy('timestamp', 'desc')
                                                 ->limit(10)
                                                 ->get()
                                                 ->map(function ($log) {
                                                     return [
                                                         'action' => $log->action,
                                                         'latitude' => (float) $log->latitude,
                                                         'longitude' => (float) $log->longitude,
                                                         'address' => $log->address ?: 'Location not available',
                                                         'timestamp' => $log->timestamp,
                                                         'workplace_name' => $log->workplace ? $log->workplace->name : null
                                                     ];
                                                 });

            return response()->json([
                'success' => true,
                'user_name' => $user->name,
                'locations' => $locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading user location details: ' . $e->getMessage(),
                'user_name' => $user->name,
                'locations' => []
            ], 500);
        }
    }

    /**
     * Bulk send password reset emails
     */
    public function bulkPasswordReset(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ], 422);
            }

            $sentCount = 0;
            $errors = [];

            foreach ($request->user_ids as $userId) {
                try {
                    $user = User::findOrFail($userId);
                    
                    // Delete any existing reset tokens for this user
                    \App\Models\PasswordReset::where('user_id', $user->id)->delete();
                    
                    // Generate a new token
                    $token = \Illuminate\Support\Str::random(60);
                    $hashedToken = \Illuminate\Support\Facades\Hash::make($token);
                    
                    // Create password reset record
                    \App\Models\PasswordReset::create([
                        'user_id' => $user->id,
                        'token' => $hashedToken,
                        'expires_at' => \Carbon\Carbon::now()->addHours(24),
                    ]);
                    
                    // Send email with reset link
                    $resetUrl = url('/password/reset/' . $token . '?email=' . urlencode($user->email));
                    
                    try {
                        \Illuminate\Support\Facades\Mail::raw(
                            "Hello {$user->name},\n\n" .
                            "You are receiving this email because your administrator has initiated a password reset for your account.\n\n" .
                            "Please click the following link to reset your password:\n" .
                            "$resetUrl\n\n" .
                            "This password reset link will expire in 24 hours.\n\n" .
                            "If you did not request this password reset, please contact your administrator immediately.\n\n" .
                            "Best regards,\n" .
                            "Curriculum Implementation System",
                            function ($message) use ($user) {
                                $message->to($user->email, $user->name)
                                        ->subject('Password Reset Request');
                            }
                        );
                        
                        $sentCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Email failed for {$user->name} ({$user->email}): " . $e->getMessage();
                    }

                } catch (\Exception $e) {
                    $user = User::find($userId);
                    $userName = $user ? $user->name : "User ID: {$userId}";
                    $errors[] = "Error processing {$userName}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Password reset emails sent to {$sentCount} user(s)",
                'sent_count' => $sentCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending password reset emails: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk change user roles
     */
    public function bulkChangeRole(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
                'role' => 'required|in:admin,user'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ], 422);
            }

            $currentUserId = Auth::id();
            $successCount = 0;
            $errors = [];

            foreach ($request->user_ids as $userId) {
                try {
                    // Prevent admin from changing their own role
                    if ($userId == $currentUserId) {
                        $errors[] = "Cannot change your own role";
                        continue;
                    }

                    $user = User::findOrFail($userId);
                    $user->role = $request->role;
                    $user->save();
                    $successCount++;

                } catch (\Exception $e) {
                    $user = User::find($userId);
                    $userName = $user ? $user->name : "User ID: {$userId}";
                    $errors[] = "Failed to update {$userName}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully updated role for {$successCount} user(s)",
                'updated_count' => $successCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during bulk role change: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete users
     */
    public function bulkDeleteUsers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ], 422);
            }

            $currentUserId = Auth::id();
            $successCount = 0;
            $errors = [];

            foreach ($request->user_ids as $userId) {
                try {
                    // Prevent admin from deleting themselves
                    if ($userId == $currentUserId) {
                        $errors[] = "Cannot delete your own account";
                        continue;
                    }

                    $user = User::findOrFail($userId);
                    
                    // Delete related records first (cascade)
                    UserWorkplace::where('user_id', $userId)->delete();
                    \App\Models\AttendanceLog::where('user_id', $userId)->delete();
                    \App\Models\Attendance::where('user_id', $userId)->delete();
                    
                    // Delete user
                    $user->delete();
                    $successCount++;

                } catch (\Exception $e) {
                    $user = User::find($userId);
                    $userName = $user ? $user->name : "User ID: {$userId}";
                    $errors[] = "Failed to delete {$userName}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$successCount} user(s)",
                'deleted_count' => $successCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during bulk deletion: ' . $e->getMessage()
            ], 500);
        }
    }
}
