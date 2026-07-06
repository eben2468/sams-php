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
            // Remove the previous logo file if one was uploaded before.
            $existing = SystemSetting::get('logo');
            if ($existing) {
                $old = dirname(__DIR__, 3) . '/public/uploads/' . ltrim($existing, '/');
                if (is_file($old)) {
                    @unlink($old);
                }
            }

            $path = $request->file('logo')->store('branding');

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
