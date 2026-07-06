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
            $uploadsDir = dirname(__DIR__, 3) . '/public/uploads';

            // Fail loudly (instead of saving a broken path) if the uploads
            // directory is not writable on this server — the usual cause of a
            // logo that "uploads" but never displays on a live host.
            if (!is_dir($uploadsDir . '/branding') && !@mkdir($uploadsDir . '/branding', 0775, true) && !is_dir($uploadsDir . '/branding')) {
                return redirect()->back()->withErrors([
                    'logo' => 'Could not save the logo: the uploads folder is not writable. On the server run: chmod -R 775 public/uploads',
                ]);
            }

            $path = $request->file('logo')->store('branding');
            $absolute = $uploadsDir . '/' . ltrim($path, '/');

            // Confirm the file actually landed on disk before committing the setting.
            if (!is_file($absolute)) {
                return redirect()->back()->withErrors([
                    'logo' => 'Could not save the logo file. Check that public/uploads is writable by the web server.',
                ]);
            }

            // New logo is safely stored — now remove the previous one.
            $existing = SystemSetting::get('logo');
            if ($existing && $existing !== $path) {
                $old = $uploadsDir . '/' . ltrim($existing, '/');
                if (is_file($old)) {
                    @unlink($old);
                }
            }

            SystemSetting::updateOrCreate(
                ['key' => 'logo'],
                ['value' => $path]
            );

            $changed['logo'] = $path;
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
