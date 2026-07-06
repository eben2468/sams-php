<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Models\AuditLog;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('students')->orderBy('name')->get();

        return response()->json(['success' => true, 'departments' => $departments]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        $department = Department::create($validated);

        AuditLog::create([
            'action'       => 'CREATE_DEPARTMENT',
            'performed_by' => Auth::id(),
            'target_type'  => 'department',
            'target_id'    => $department->id,
            'metadata'     => ['name' => $department->name],
            'timestamp'    => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Department created successfully.',
                'department' => $department,
            ], 201);
        }

        return redirect()->back()->with('success', 'Department created successfully.');
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $id,
        ]);

        $department->update($validated);

        AuditLog::create([
            'action'       => 'UPDATE_DEPARTMENT',
            'performed_by' => Auth::id(),
            'target_type'  => 'department',
            'target_id'    => $department->id,
            'metadata'     => ['name' => $department->name],
            'timestamp'    => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Department updated successfully.',
                'department' => $department,
            ]);
        }

        return redirect()->back()->with('success', 'Department updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        if ($department->students()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department with existing students.',
            ], 400);
        }

        if ($department->programs()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department with existing programs.',
            ], 400);
        }

        AuditLog::create([
            'action'       => 'DELETE_DEPARTMENT',
            'performed_by' => Auth::id(),
            'target_type'  => 'department',
            'target_id'    => $id,
            'metadata'     => ['name' => $department->name],
            'timestamp'    => now(),
        ]);

        $department->delete();

        return response()->json(['success' => true, 'message' => 'Department deleted successfully.']);
    }
}
