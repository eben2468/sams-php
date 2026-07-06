<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Hash;
use App\Core\Request;
use App\Core\ValidationException;
use App\Models\AuditLog;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        AuditLog::create([
            'action'      => 'LOGIN',
            'performed_by' => $user->id,
            'target_type' => 'user',
            'target_id'   => $user->id,
            'timestamp'   => now(),
        ]);

        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'user'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ],
            ]);
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLog::create([
                'action'       => 'LOGOUT',
                'performed_by' => Auth::id(),
                'target_type'  => 'user',
                'target_id'    => Auth::id(),
                'timestamp'    => now(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);
        }

        return redirect()->route('login');
    }

    public function me(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'user'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role,
                'created_at' => $user->created_at,
            ],
        ]);
    }
}
