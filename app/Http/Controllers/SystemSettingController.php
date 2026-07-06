<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Program;
use App\Models\Semester;
use App\Models\SystemSetting;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->pluck('value', 'key');

        $departments = Department::orderBy('name')->get();
        $programs = Program::with('department')->orderBy('name')->get();

        $semesters = Semester::orderBy('start_date', 'desc')->get();
        $activeSemester = Semester::active()->first();

        return view('settings.index', compact('settings', 'departments', 'programs', 'semesters', 'activeSemester'));
    }

    public function updateSemester(Request $request)
    {
        $validated = $request->validate([
            'active_semester' => 'required|string|max:255',
        ]);

        SystemSetting::updateOrCreate(
            ['key' => 'active_semester'],
            ['value' => $validated['active_semester']]
        );

        AuditLog::create([
            'action'       => 'UPDATE_SEMESTER',
            'performed_by' => Auth::id(),
            'target_type'  => 'system_setting',
            'metadata'     => ['semester' => $validated['active_semester']],
            'timestamp'    => now(),
        ]);

        return redirect()->back()->with('success', 'Active semester updated successfully. Note: Please use the new semester management system below.');
    }

    public function updateLogo(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:100',
            'logo'     => 'nullable|image|max:2048',
        ]);

        SystemSetting::updateOrCreate(
            ['key' => 'app_name'],
            ['value' => $validated['app_name']]
        );

        $changed = ['app_name' => $validated['app_name']];

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');

            // Read the uploaded bytes straight from the temp file so storing the
            // logo does not depend on public/uploads being writable on the host.
            $bytes = @file_get_contents($file->getRealPath());
            if ($bytes === false || $bytes === '') {
                return redirect()->back()->withErrors([
                    'logo' => 'Could not read the uploaded logo. Please try a different image.',
                ]);
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

            // Primary store: database (filesystem-independent, survives deploys).
            ensure_app_files_table();
            try {
                \App\Core\Database::statement(
                    'INSERT INTO `app_files` (`name`, `mime`, `data`) VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE `mime` = VALUES(`mime`), `data` = VALUES(`data`)',
                    ['logo', $mime, $bytes]
                );
            } catch (\Throwable $e) {
                return redirect()->back()->withErrors([
                    'logo' => 'Could not save the logo to the database. Please contact the administrator to run the app_files migration.',
                ]);
            }

            // Best-effort: also keep a file copy so the static/media path works
            // too. Ignored if the uploads directory is not writable.
            try {
                $file->store('branding');
            } catch (\Throwable $e) {
                // no-op — the database copy above is authoritative
            }

            // Marker so the file-based fallback knows the logo lives in the DB.
            SystemSetting::updateOrCreate(['key' => 'logo'], ['value' => 'db']);

            $changed['logo'] = 'db (' . strlen($bytes) . ' bytes)';
        }

        AuditLog::create([
            'action'       => 'UPDATE_BRANDING',
            'performed_by' => Auth::id(),
            'target_type'  => 'system_setting',
            'metadata'     => $changed,
            'timestamp'    => now(),
        ]);

        return redirect()->back()->with('success', 'Branding updated successfully.');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings'   => 'required|array',
            'settings.*' => 'required|string',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            SystemSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        AuditLog::create([
            'action'       => 'UPDATE_SETTINGS',
            'performed_by' => Auth::id(),
            'target_type'  => 'system_setting',
            'metadata'     => ['keys' => array_keys($validated['settings'])],
            'timestamp'    => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Settings updated successfully.']);
    }
}
