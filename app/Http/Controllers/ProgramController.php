<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Models\AuditLog;
use App\Models\Program;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::with('department')->withCount('students')->orderBy('name')->get();

        return response()->json(['success' => true, 'programs' => $programs]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255|unique:programs,name',
            'department_id' => 'required|exists:departments,id',
        ]);

        $program = Program::create($validated);

        AuditLog::create([
            'action'       => 'CREATE_PROGRAM',
            'performed_by' => Auth::id(),
            'target_type'  => 'program',
            'target_id'    => $program->id,
            'metadata'     => ['name' => $program->name],
            'timestamp'    => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Program created successfully.',
                'program' => $program->load('department'),
            ], 201);
        }

        return redirect()->back()->with('success', 'Program created successfully.');
    }

    public function update(Request $request, $id)
    {
        $program = Program::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255|unique:programs,name,' . $id,
            'department_id' => 'required|exists:departments,id',
        ]);

        $program->update($validated);

        AuditLog::create([
            'action'       => 'UPDATE_PROGRAM',
            'performed_by' => Auth::id(),
            'target_type'  => 'program',
            'target_id'    => $program->id,
            'metadata'     => ['name' => $program->name],
            'timestamp'    => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Program updated successfully.',
                'program' => $program->load('department'),
            ]);
        }

        return redirect()->back()->with('success', 'Program updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $program = Program::findOrFail($id);

        if ($program->students()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete program with existing students.',
            ], 400);
        }

        AuditLog::create([
            'action'       => 'DELETE_PROGRAM',
            'performed_by' => Auth::id(),
            'target_type'  => 'program',
            'target_id'    => $id,
            'metadata'     => ['name' => $program->name],
            'timestamp'    => now(),
        ]);

        $program->delete();

        return response()->json(['success' => true, 'message' => 'Program deleted successfully.']);
    }
}
