<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Hash;
use App\Core\Request;
use App\Models\AuditLog;
use App\Models\User;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|max:2048',
        ]);

        $changes = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        // --- Optional password change --------------------------------------
        $newPassword = (string) $request->input('password', '');
        if ($newPassword !== '') {
            $errors = [];

            $current = (string) $request->input('current_password', '');
            if ($current === '' || !Hash::check($current, $user->password)) {
                $errors['current_password'] = 'Your current password is incorrect.';
            }
            if (strlen($newPassword) < 6) {
                $errors['password'] = 'The new password must be at least 6 characters.';
            }
            if ($newPassword !== (string) $request->input('password_confirmation', '')) {
                $errors['password_confirmation'] = 'The password confirmation does not match.';
            }

            if ($errors) {
                return redirect()->back()->withErrors($errors)->withInput($request->all());
            }

            $changes['password'] = Hash::make($newPassword);
        }

        // --- Optional avatar upload (stored in the DB) ---------------------
        if ($request->hasFile('avatar')) {
            $file  = $request->file('avatar');
            $bytes = @file_get_contents($file->getRealPath());
            if ($bytes === false || $bytes === '') {
                return redirect()->back()->withErrors([
                    'avatar' => 'Could not read the uploaded image. Please try another one.',
                ])->withInput($request->all());
            }

            $ext  = strtolower($file->getClientOriginalExtension());
            $mime = [
                'png'  => 'image/png',
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',
                'bmp'  => 'image/bmp',
            ][$ext] ?? 'image/png';

            Database::statement(
                'INSERT INTO `app_files` (`name`, `mime`, `data`) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE `mime` = VALUES(`mime`), `data` = VALUES(`data`)',
                ['avatar-' . $user->id, $mime, $bytes]
            );

            $changes['avatar'] = 'db';
        }

        $user->update($changes);

        AuditLog::create([
            'action'       => 'UPDATE_PROFILE',
            'performed_by' => $user->id,
            'target_type'  => 'user',
            'target_id'    => $user->id,
            'metadata'     => ['email' => $user->email],
            'timestamp'    => now(),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Your profile has been updated.');
    }
}
