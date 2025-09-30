<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Workplace;
use App\Models\UserWorkplace;
use Illuminate\Support\Facades\Validator;

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
