<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Workplace;
use App\Models\UserWorkplace;
use App\Models\Attendance;
use App\Models\AdminActivityLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
                // Use address if available, otherwise use workplace address
                $address = $latestLog->address;
                if (!$address && $latestLog->workplace) {
                    $address = $latestLog->workplace->address;
                }
                
                $latestAttendance[$user->id] = [
                    'action' => $latestLog->action,
                    'address' => $address,
                    'latitude' => $latestLog->latitude,
                    'longitude' => $latestLog->longitude,
                    'workplace_name' => $latestLog->workplace ? $latestLog->workplace->name : null,
                    'workplace_address' => $latestLog->workplace ? $latestLog->workplace->address : null,
                    'timestamp' => $latestLog->timestamp,
                    'device' => $latestLog->device ?? 'Unknown'
                ];
            }
        }
        
        // Get system settings
        $settings = SystemSetting::getAll();
        
        return view('admin.dashboard', compact('users', 'workplaces', 'latestAttendance', 'settings'));
    }

    /**
     * Get all users for API (with optional search)
     */
    public function getUsers(Request $request)
    {
        $search = $request->input('search', '');
        
        $query = User::select('id', 'name', 'email', 'role');
        
        // Apply search filter if provided
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        
        $users = $query->orderBy('name', 'asc')->get();
        
        // If it's a search request, return simple array
        if ($search) {
            return response()->json($users);
        }
        
        // Otherwise return with success wrapper
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

        // Log activity
        $this->logActivity(
            'create_user',
            "Created new user: {$user->name} ({$user->email})",
            'User',
            $user->id,
            ['user' => $user->only(['name', 'email', 'role'])]
        );

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

        $oldData = $user->only(['name', 'email', 'role']);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        $user->update($updateData);

        // Log activity
        $changes = [];
        if ($oldData['name'] !== $request->name) $changes[] = 'name';
        if ($oldData['email'] !== $request->email) $changes[] = 'email';
        if ($oldData['role'] !== $request->role) $changes[] = 'role';
        if ($request->filled('password')) $changes[] = 'password';

        $this->logActivity(
            'update_user',
            "Updated user: {$user->name} (" . implode(', ', $changes) . ")",
            'User',
            $user->id,
            ['old' => $oldData, 'new' => $user->only(['name', 'email', 'role'])]
        );

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

        $userName = $user->name;
        $userId = $user->id;

        // Remove all workplace assignments
        $user->workplaces()->detach();
        
        $user->delete();

        // Log activity
        $this->logActivity(
            'delete_user',
            "Deleted user: {$userName}",
            'User',
            $userId
        );

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

        // Log activity
        $this->logActivity(
            'create_workplace',
            "Created new workplace: {$workplace->name}",
            'Workplace',
            $workplace->id,
            ['workplace' => $workplace->only(['name', 'address', 'latitude', 'longitude', 'radius'])]
        );

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

        $oldData = $workplace->only(['name', 'address', 'latitude', 'longitude', 'radius', 'is_active']);

        $workplace->update($request->all());

        // Log activity
        $changes = [];
        foreach ($oldData as $key => $value) {
            if ($workplace->$key != $value) {
                $changes[] = $key;
            }
        }

        $this->logActivity(
            'update_workplace',
            "Updated workplace: {$workplace->name} (" . implode(', ', $changes) . ")",
            'Workplace',
            $workplace->id,
            ['old' => $oldData, 'new' => $workplace->only(['name', 'address', 'latitude', 'longitude', 'radius', 'is_active'])]
        );

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

        $workplaceName = $workplace->name;
        $workplaceId = $workplace->id;

        $workplace->delete();

        // Log activity
        $this->logActivity(
            'delete_workplace',
            "Deleted workplace: {$workplaceName}",
            'Workplace',
            $workplaceId
        );

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

        // Log activity
        $user = User::find($request->user_id);
        $workplace = Workplace::find($request->workplace_id);
        $this->logActivity(
            'assign_user_workplace',
            "Assigned user '{$user->name}' to workplace '{$workplace->name}'" . ($request->is_primary ? ' (primary)' : ''),
            'UserWorkplace',
            null,
            ['user_id' => $user->id, 'workplace_id' => $workplace->id, 'role' => $request->role ?? 'employee']
        );

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

        // Get user and workplace info before deleting
        $user = User::find($request->user_id);
        $workplace = Workplace::find($request->workplace_id);

        $assignment->delete();

        // Log activity
        $this->logActivity(
            'remove_user_workplace',
            "Removed user '{$user->name}' from workplace '{$workplace->name}'",
            'UserWorkplace',
            null,
            ['user_id' => $user->id, 'workplace_id' => $workplace->id]
        );

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
                                                         'workplace_name' => $log->workplace ? $log->workplace->name : null,
                                                         'workplace_address' => $log->workplace ? $log->workplace->address : null
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
                        'expires_at' => \Carbon\Carbon::now()->addHours(1), 
                    ]);
                    
                    // Send email with reset link
                    $resetUrl = url('/password/reset/' . $token . '?email=' . urlencode($user->email));
                    
                    try {
                        \Illuminate\Support\Facades\Mail::raw(
                            "Hello {$user->name},\n\n" .
                            "You are receiving this email because your administrator has initiated a password reset for your account.\n\n" .
                            "Please click the following link to reset your password:\n" .
                            "$resetUrl\n\n" .
                            "This password reset link will expire in 1 hour.\n\n" .
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

    /**
     * Get admin activity logs
     */
    public function getActivityLogs(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $search = $request->get('search');
        $action = $request->get('action');
        
        $query = AdminActivityLog::with('admin:id,name,email');
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                  ->orWhere('action', 'like', '%' . $search . '%')
                  ->orWhere('ip_address', 'like', '%' . $search . '%')
                  ->orWhereHas('admin', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }
        
        // Apply action filter
        if ($action) {
            $query->where('action', $action);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }

    /**
     * Get attendance logs (assigned vs non-assigned workplaces)
     */
    public function getAttendanceLogs(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $search = $request->get('search');
        $type = $request->get('type'); // 'assigned' or 'non_assigned'
        $date = $request->get('date', now()->format('Y-m-d'));
        
        $query = Attendance::with(['user:id,name,email', 'workplace:id,name,address'])
            ->whereDate('date', $date);
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                })
                ->orWhereHas('workplace', function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            });
        }
        
        // Apply type filter
        if ($type === 'assigned') {
            $query->where('is_assigned_workplace', true);
        } elseif ($type === 'non_assigned') {
            $query->where('is_assigned_workplace', false);
        }
        
        $logs = $query->orderBy('check_in_time', 'desc')->paginate($perPage);
        
        // Calculate stats for the date
        $stats = [
            'total' => Attendance::whereDate('date', $date)->count(),
            'assigned' => Attendance::whereDate('date', $date)->where('is_assigned_workplace', true)->count(),
            'non_assigned' => Attendance::whereDate('date', $date)->where('is_assigned_workplace', false)->count(),
        ];
        
        return response()->json([
            'success' => true,
            'logs' => $logs,
            'stats' => $stats
        ]);
    }

    /**
     * Update admin account with double security
     */
    public function updateAdminAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'security_phrase' => 'required|string',
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . Auth::id(),
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $admin = Auth::user();

        // First security check: current password
        if (!Hash::check($request->current_password, $admin->password)) {
            AdminActivityLog::log(
                'failed_admin_update',
                'Failed attempt to update admin account - incorrect password',
                'AdminAccount',
                $admin->id
            );
            
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.'
            ], 403);
        }

        // Second security check: security phrase (must be "CONFIRM UPDATE ADMIN")
        if ($request->security_phrase !== 'CONFIRM UPDATE ADMIN') {
            AdminActivityLog::log(
                'failed_admin_update',
                'Failed attempt to update admin account - incorrect security phrase',
                'AdminAccount',
                $admin->id
            );
            
            return response()->json([
                'success' => false,
                'message' => 'Security phrase is incorrect. Please type "CONFIRM UPDATE ADMIN" exactly.'
            ], 403);
        }

        $changes = [];
        $oldData = [
            'name' => $admin->name,
            'email' => $admin->email,
        ];

        // Update name if provided
        if ($request->has('name') && $request->name !== $admin->name) {
            $changes[] = 'Name changed from "' . $admin->name . '" to "' . $request->name . '"';
            $admin->name = $request->name;
        }

        // Update email if provided
        if ($request->has('email') && $request->email !== $admin->email) {
            $changes[] = 'Email changed from "' . $admin->email . '" to "' . $request->email . '"';
            $admin->email = $request->email;
        }

        // Update password if provided
        if ($request->filled('new_password')) {
            $changes[] = 'Password changed';
            $admin->password = Hash::make($request->new_password);
        }

        if (empty($changes)) {
            return response()->json([
                'success' => false,
                'message' => 'No changes were made.'
            ], 400);
        }

        $admin->save();

        $newData = [
            'name' => $admin->name,
            'email' => $admin->email,
        ];

        // Log the activity
        AdminActivityLog::log(
            'update_admin_account',
            'Admin account updated: ' . implode(', ', $changes),
            'AdminAccount',
            $admin->id,
            [
                'old' => $oldData,
                'new' => $newData,
                'changes' => $changes
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Admin account updated successfully.',
            'changes' => $changes
        ]);
    }

    /**
     * Helper method to log admin activities
     */
    protected function logActivity($action, $description, $entityType = null, $entityId = null, $changes = null)
    {
        AdminActivityLog::log($action, $description, $entityType, $entityId, $changes);
    }

    /**
     * Get all system settings
     */
    public function getSettings()
    {
        return response()->json([
            'success' => true,
            'settings' => SystemSetting::getAll()
        ]);
    }

    /**
     * Update a system setting
     */
    public function updateSetting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        SystemSetting::set($request->key, $request->value);

        // Log activity
        $this->logActivity(
            'update_setting',
            "Updated system setting: {$request->key} to {$request->value}",
            'SystemSetting',
            null,
            ['key' => $request->key, 'value' => $request->value]
        );

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully'
        ]);
    }

    /**
     * Update manual entry code
     */
    public function updateManualEntryCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_password' => 'required|string',
            'key' => 'required|string|in:manual_entry_code',
            'value' => 'required|string|min:4|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify admin password
        if (!Hash::check($request->admin_password, Auth::user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid admin password'
            ], 401);
        }

        // Update the setting
        SystemSetting::set($request->key, $request->value);

        // Log activity
        $this->logActivity(
            'update_manual_entry_code',
            "Updated manual entry access code",
            'SystemSetting',
            null,
            ['key' => $request->key, 'new_code' => $request->value]
        );

        return response()->json([
            'success' => true,
            'message' => 'Manual entry code updated successfully'
        ]);
    }

    /**
     * Get attendance statistics for admin dashboard
     */
    public function getAttendanceStats()
    {
        try {
            $today = now()->startOfDay();
            $lateTimeThreshold = now()->setTime(9, 0, 0); // 9:00 AM
            
            // Get all attendance logs for today
            $todayLogs = \App\Models\AttendanceLog::whereDate('timestamp', $today)
                ->with(['user', 'workplace'])
                ->orderBy('timestamp', 'asc')
                ->get();

            // Also load Attendance rows (with logs) for today so we can expand per-pair rows
            $attendancesToday = \App\Models\Attendance::with(['user','workplace','logs'])
                ->where('date', $today->format('Y-m-d'))
                ->get();

            // Use report controller helper to expand attendances into per-pair rows (prefer per-log workplace)
            $reportController = new \App\Http\Controllers\AdminReportController();
            $expandedRows = [];
            try {
                $expandedRows = $reportController->expandAttendancesToRows($attendancesToday);
            } catch (\Exception $e) {
                // Fallback: empty rows on error
                $expandedRows = collect([]);
            }

            // Group logs by user
            $userLogs = $todayLogs->groupBy('user_id');
            
            $attendanceData = [];
            $totalCheckIns = 0;
            $lateArrivals = 0;
            $totalWorkHours = 0;
            $usersWithHours = 0;

            foreach ($userLogs as $userId => $logs) {
                $user = $logs->first()->user;
                if (!$user) continue;

                $checkIn = $logs->firstWhere('action', 'check_in');
                $checkOut = $logs->firstWhere('action', 'check_out');
                $breakStart = $logs->firstWhere('action', 'break_start');
                $breakEnd = $logs->firstWhere('action', 'break_end');

                if ($checkIn) {
                    $totalCheckIns++;
                }

                // Check if late (after 9:00 AM)
                $isLate = false;
                if ($checkIn && $checkIn->timestamp->gt($lateTimeThreshold)) {
                    $isLate = true;
                    $lateArrivals++;
                }

                // Calculate work hours
                $workHours = 0;
                $breakDuration = 0;
                $status = 'No activity';
                
                if ($checkIn && $checkOut) {
                    // Full day worked
                    $workMinutes = $checkIn->timestamp->diffInMinutes($checkOut->timestamp);
                    
                    // Subtract break time if exists
                    if ($breakStart && $breakEnd) {
                        $breakDuration = $breakStart->timestamp->diffInMinutes($breakEnd->timestamp);
                        $workMinutes -= $breakDuration;
                    }
                    
                    $workHours = round($workMinutes / 60, 2);
                    $totalWorkHours += $workHours;
                    $usersWithHours++;
                    $status = 'Completed';
                } elseif ($checkIn && !$checkOut) {
                    // Still working
                    $workMinutes = $checkIn->timestamp->diffInMinutes(now());
                    
                    // Subtract break time if currently on break or break completed
                    if ($breakStart && $breakEnd) {
                        $breakDuration = $breakStart->timestamp->diffInMinutes($breakEnd->timestamp);
                        $workMinutes -= $breakDuration;
                    } elseif ($breakStart && !$breakEnd) {
                        // Currently on break
                        $workMinutes = $checkIn->timestamp->diffInMinutes($breakStart->timestamp);
                        $status = 'On Break';
                    }
                    
                    if ($status !== 'On Break') {
                        $status = 'Working';
                    }
                    
                    $workHours = round($workMinutes / 60, 2);
                    // Include currently working employees in average calculation
                    $totalWorkHours += $workHours;
                    $usersWithHours++;
                }

                // Calculate break duration in minutes
                $breakMinutes = 0;
                if ($breakStart && $breakEnd) {
                    $breakMinutes = abs((int) $breakStart->timestamp->diffInMinutes($breakEnd->timestamp));
                } elseif ($breakStart && !$breakEnd) {
                    $breakMinutes = abs((int) $breakStart->timestamp->diffInMinutes(now()));
                }

                // Format late_by text
                $lateByText = null;
                if ($isLate && $checkIn) {
                    // Get absolute integer minutes - diffInMinutes returns float
                    $lateMinutes = abs((int) $checkIn->timestamp->diffInMinutes($lateTimeThreshold));
                    
                    if ($lateMinutes >= 60) {
                        $lateHours = floor($lateMinutes / 60);
                        $remainingMinutes = $lateMinutes % 60;
                        if ($remainingMinutes > 0) {
                            $lateByText = $lateHours . ' hr ' . $remainingMinutes . ' min';
                        } else {
                            $lateByText = $lateHours . ($lateHours == 1 ? ' hr' : ' hrs');
                        }
                    } else {
                        $lateByText = $lateMinutes . ' min';
                    }
                }

                $attendanceData[] = [
                    'user_id' => $userId,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'check_in' => $checkIn ? $checkIn->timestamp->format('g:i A') : null,
                    'check_in_raw' => $checkIn ? $checkIn->timestamp : null,
                    'check_out' => $checkOut ? $checkOut->timestamp->format('g:i A') : null,
                    'check_out_raw' => $checkOut ? $checkOut->timestamp : null,
                    'break_start' => $breakStart ? $breakStart->timestamp->format('g:i A') : null,
                    'break_end' => $breakEnd ? $breakEnd->timestamp->format('g:i A') : null,
                    'break_duration' => $breakMinutes > 0 ? round($breakMinutes) . ' min' : 'N/A',
                    'work_hours' => $workHours > 0 ? $workHours . ' hrs' : '0 hrs',
                    'work_hours_raw' => $workHours,
                    'is_late' => $isLate,
                    'late_by' => $lateByText,
                    'status' => $status,
                    'workplace' => $checkIn && $checkIn->workplace ? $checkIn->workplace->name : 'N/A',
                    'location' => $checkIn ? $checkIn->address : null,
                    'workplace' => $checkIn && $checkIn->workplace ? $checkIn->workplace->name : 'N/A',
                ];
            }

            // If we were able to expand per-pair rows, prefer those for the overview table so
            // special attendances (multiple pairs) are shown individually.
            if (isset($expandedRows) && $expandedRows->count() > 0) {
                $mapped = [];
                foreach ($expandedRows as $r) {
                    // $r is an object with: user (model), workplace (model or null), check_in_time, check_out_time, status
                    $user = $r->user ?? null;
                    $wp = $r->workplace ?? null;

                    $checkInRaw = $r->check_in_time ? (string)$r->check_in_time : null;
                    $checkOutRaw = $r->check_out_time ? (string)$r->check_out_time : null;

                    // Calculate work minutes
                    $workMinutes = 0;
                    if ($checkInRaw && $checkOutRaw) {
                        try {
                            $ci = Carbon::parse($checkInRaw);
                            $co = Carbon::parse($checkOutRaw);
                            $workMinutes = $ci->diffInMinutes($co);
                            if (!empty($r->break_duration)) {
                                $workMinutes -= (int)$r->break_duration;
                            }
                        } catch (\Exception $e) {
                            $workMinutes = 0;
                        }
                    }

                    // Late calculation (after 9:00)
                    $isLate = false;
                    $lateByText = null;
                    if ($checkInRaw) {
                        try {
                            $ci = Carbon::parse($checkInRaw);
                            $threshold = Carbon::parse($r->date)->setTime(9,0,0);
                            if ($ci->gt($threshold)) {
                                $isLate = true;
                                $lateMinutes = $ci->diffInMinutes($threshold);
                                if ($lateMinutes >= 60) {
                                    $lateByText = floor($lateMinutes/60) . ' hr ' . ($lateMinutes%60) . ' min';
                                } else {
                                    $lateByText = $lateMinutes . ' min';
                                }
                            }
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }

                    $mapped[] = [
                        'user_id' => $user ? $user->id : null,
                        'user_name' => $user ? $user->name : 'Unknown',
                        'user_email' => $user ? $user->email : '',
                        'check_in' => $checkInRaw ? Carbon::parse($checkInRaw)->format('g:i A') : null,
                        'check_in_raw' => $checkInRaw ? Carbon::parse($checkInRaw) : null,
                        'check_out' => $checkOutRaw ? Carbon::parse($checkOutRaw)->format('g:i A') : null,
                        'check_out_raw' => $checkOutRaw ? Carbon::parse($checkOutRaw) : null,
                        'break_start' => null,
                        'break_end' => null,
                        'break_duration' => !empty($r->break_duration) ? $r->break_duration : 'N/A',
                        'work_hours' => $workMinutes > 0 ? round($workMinutes/60,2) . ' hrs' : '0 hrs',
                        'work_hours_raw' => $workMinutes > 0 ? round($workMinutes/60,2) : 0,
                        'is_late' => $isLate,
                        'late_by' => $lateByText,
                        'status' => $r->status ?? 'N/A',
                        'workplace' => $wp ? ($wp->name ?? $wp) : ($r->workplace ?? 'N/A'),
                        'location' => null,
                    ];
                }

                // Replace attendance data with mapped per-pair rows
                $attendanceData = $mapped;
            }
            // Calculate average hours
            $avgHours = $usersWithHours > 0 ? round($totalWorkHours / $usersWithHours, 1) : 0;

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_checkins' => $totalCheckIns,
                    'late_arrivals' => $lateArrivals,
                    'average_hours' => $avgHours
                ],
                'attendance' => $attendanceData,
                // flattened per-pair rows for UI (each row contains user/workplace/check_in_time/check_out_time)
                'rows' => $expandedRows
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attendance statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save notification settings for auto check-out and reminders
     */
    public function saveNotificationSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_type' => 'required|in:email,sms,both,none',
            'sms_api_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Update notification type
        SystemSetting::set('notification_type', $request->notification_type);

        // Update SMS API URL if provided
        if ($request->has('sms_api_url') && !empty($request->sms_api_url)) {
            SystemSetting::set('sms_api_url', $request->sms_api_url);
        }

        // Log activity
        $this->logActivity(
            'update_notification_settings',
            "Updated notification settings: type={$request->notification_type}",
            'SystemSetting',
            null,
            [
                'notification_type' => $request->notification_type,
                'sms_api_url' => $request->sms_api_url
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification settings saved successfully'
        ]);
    }

    /**
     * Get notification settings
     */
    public function getNotificationSettings()
    {
        $notificationType = SystemSetting::get('notification_type', 'email');
        $smsApiUrl = SystemSetting::get('sms_api_url', 'https://sms.cisdepedcavite.org/api/send');
        $autoCheckoutTime = SystemSetting::get('auto_checkout_time', '18:00');
        $reminderTime = SystemSetting::get('reminder_time', '16:30');

        return response()->json([
            'success' => true,
            'notification_type' => $notificationType,
            'sms_api_url' => $smsApiUrl,
            'auto_checkout_time' => $autoCheckoutTime,
            'reminder_time' => $reminderTime
        ]);
    }

    /**
     * Test notification (send test email/SMS to admin)
     */
    public function testNotification(Request $request)
    {
        try {
            $user = Auth::user();
            $notificationType = $request->input('notification_type', SystemSetting::get('notification_type', 'email'));
            
            $message = "This is a test notification from CIS-AM Auto Check-Out System. If you received this, your notification settings are working correctly!";
            $subject = "Test Notification - CIS-AM";

            $success = false;
            $details = [];

            // Send Email
            if ($notificationType === 'email' || $notificationType === 'both') {
                try {
                    \Mail::raw($message, function($mail) use ($user, $subject) {
                        $mail->to($user->email)
                             ->subject($subject);
                    });
                    $success = true;
                    $details[] = 'Email sent to ' . $user->email;
                } catch (\Exception $e) {
                    $details[] = 'Email failed: ' . $e->getMessage();
                }
            }

            // Send SMS
            if ($notificationType === 'sms' || $notificationType === 'both') {
                $smsApiUrl = SystemSetting::get('sms_api_url', env('SMS_API_URL'));
                $smsApiKey = env('SMS_API_KEY');
                
                // Use user's phone number or fallback to test phone from env
                $phoneNumber = $user->phone_number ?: env('SMS_TEST_PHONE');
                
                if ($phoneNumber) {
                    try {
                        // Send SMS with Bearer token authentication
                        $response = \Http::withOptions([
                            'verify' => false
                        ])->withHeaders([
                            'Authorization' => 'Bearer ' . $smsApiKey
                        ])->post($smsApiUrl, [
                            'gatewayUrl' => 'api.sms-gate.app',
                            'phone' => $phoneNumber,
                            'message' => $message,
                            'senderName' => 'CIS-AM System'
                        ]);
                        
                        if ($response->successful()) {
                            $success = true;
                            $details[] = 'SMS sent to ' . $phoneNumber . ($user->phone_number ? '' : ' (test number)');
                        } else {
                            $details[] = 'SMS failed: ' . $response->body();
                        }
                    } catch (\Exception $e) {
                        $details[] = 'SMS failed: ' . $e->getMessage();
                    }
                } else {
                    $details[] = 'SMS skipped: No phone number in profile and no SMS_TEST_PHONE in .env';
                }
            }

            // Log activity
            $this->logActivity(
                'test_notification',
                "Tested notification system with type: {$notificationType}",
                'SystemSetting',
                null,
                ['notification_type' => $notificationType, 'details' => $details]
            );

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Test notification sent successfully!' : 'Failed to send test notification',
                'details' => $details
            ]);

        } catch (\Exception $e) {
            \Log::error('Test notification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}




