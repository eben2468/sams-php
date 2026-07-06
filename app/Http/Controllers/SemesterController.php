<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Models\AuditLog;
use App\Models\Semester;

class SemesterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after:start_date',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        if ($validated['is_active'] ?? false) {
            Semester::query()->update(['is_active' => false]);
        }

        $semester = Semester::create($validated);

        AuditLog::create([
            'action'       => 'CREATE_SEMESTER',
            'performed_by' => Auth::id(),
            'target_type'  => 'semester',
            'target_id'    => $semester->id,
            'metadata'     => ['name' => $semester->name],
            'timestamp'    => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Semester created successfully.',
                'semester' => $semester,
            ], 201);
        }

        return redirect()->back()->with('success', 'Semester created successfully.');
    }

    public function update(Request $request, $id)
    {
        $semester = Semester::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after:start_date',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        if ($validated['is_active'] ?? false) {
            Semester::where('id', '!=', $id)->update(['is_active' => false]);
        }

        $semester->update($validated);

        AuditLog::create([
            'action'       => 'UPDATE_SEMESTER',
            'performed_by' => Auth::id(),
            'target_type'  => 'semester',
            'target_id'    => $semester->id,
            'metadata'     => ['name' => $semester->name],
            'timestamp'    => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Semester updated successfully.',
                'semester' => $semester,
            ]);
        }

        return redirect()->back()->with('success', 'Semester updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $semester = Semester::findOrFail($id);

        if ($semester->events()->count() > 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete semester with existing events.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete semester with existing events.');
        }

        AuditLog::create([
            'action'       => 'DELETE_SEMESTER',
            'performed_by' => Auth::id(),
            'target_type'  => 'semester',
            'target_id'    => $id,
            'metadata'     => ['name' => $semester->name],
            'timestamp'    => now(),
        ]);

        $semester->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Semester deleted successfully.']);
        }

        return redirect()->back()->with('success', 'Semester deleted successfully.');
    }

    public function setActive(Request $request, $id)
    {
        $semester = Semester::findOrFail($id);

        Semester::query()->update(['is_active' => false]);
        $semester->update(['is_active' => true]);

        AuditLog::create([
            'action'       => 'SET_ACTIVE_SEMESTER',
            'performed_by' => Auth::id(),
            'target_type'  => 'semester',
            'target_id'    => $semester->id,
            'metadata'     => ['name' => $semester->name],
            'timestamp'    => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Active semester updated successfully.',
                'semester' => $semester,
            ]);
        }

        return redirect()->back()->with('success', 'Active semester updated successfully.');
    }

    public function getActive()
    {
        $semester = Semester::active()->first();

        return response()->json(['success' => true, 'semester' => $semester]);
    }

    public function index(Request $request)
    {
        $semesters = Semester::orderBy('start_date', 'desc')->get();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'semesters' => $semesters]);
        }

        return response()->json(['success' => true, 'semesters' => $semesters]);
    }
}
