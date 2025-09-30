<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Workplace;
use App\Models\UserWorkplace;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        $users = User::with('workplaces')->get();
        $workplaces = Workplace::withCount('users')->get();
        
        return view('admin.dashboard', compact('users', 'workplaces'));
    }

    /**
     * Get workplace data for editing
     */
    public function getWorkplace(Workplace $workplace)
    {
        return response()->json([
            'success' => true,
            'workplace' => $workplace->load('users')
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
}
