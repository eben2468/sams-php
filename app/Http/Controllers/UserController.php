<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Hash;
use App\Core\Request;
use App\Models\AuditLog;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->orderBy('name')->paginate(20, (int) $request->input('page', 1));

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'users'      => $users->items(),
                'pagination' => [
                    'total'       => $users->total(),
                    'page'        => $users->currentPage(),
                    'limit'       => $users->perPage(),
                    'total_pages' => $users->lastPage(),
                ],
            ]);
        }

        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'role'      => 'required|in:admin,officer,supervisor',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        AuditLog::create([
            'action'       => 'CREATE_USER',
            'performed_by' => Auth::id(),
            'target_type'  => 'user',
            'target_id'    => $user->id,
            'metadata'     => ['email' => $user->email, 'role' => $user->role],
            'timestamp'    => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'user'    => $user,
            ], 201);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $id,
            'password'  => 'nullable|string|min:6',
            'role'      => 'required|in:admin,officer,supervisor',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        AuditLog::create([
            'action'       => 'UPDATE_USER',
            'performed_by' => Auth::id(),
            'target_type'  => 'user',
            'target_id'    => $user->id,
            'metadata'     => ['email' => $user->email],
            'timestamp'    => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'user'    => $user,
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ((int) $user->id === (int) Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.',
            ], 400);
        }

        AuditLog::create([
            'action'       => 'DELETE_USER',
            'performed_by' => Auth::id(),
            'target_type'  => 'user',
            'target_id'    => $id,
            'metadata'     => ['email' => $user->email],
            'timestamp'    => now(),
        ]);

        $user->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
        }

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
