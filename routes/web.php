<?php

/**
 * Web routes. $router is provided by public/index.php.
 *
 * @var \App\Core\Router $router
 */

use App\Core\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\UserController;

// Root: redirect to dashboard or login.
$router->get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Public media proxy — streams files from public/uploads through PHP so that
// logos and photos display even on hosts that don't serve uploads statically.
$router->get('/media', [MediaController::class, 'show'], 'media');

// Public branding logo — served from the database (filesystem-independent).
$router->get('/branding/logo', [MediaController::class, 'logo'], 'branding.logo');

// Public user avatar — served from the database.
$router->get('/avatar/{id}', [MediaController::class, 'avatar'], 'avatar');

// Favicon built from the logo (scaled to fill so it looks large in the tab).
$router->get('/branding/favicon.svg', [MediaController::class, 'favicon'], 'branding.favicon');

// Guest routes
$router->group(['middleware' => ['guest']], function ($router) {
    $router->get('/login', [AuthController::class, 'showLogin'], 'login');
    $router->post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
$router->group(['middleware' => ['auth']], function ($router) {
    $router->post('/logout', [AuthController::class, 'logout'], 'logout');

    // Dashboard
    $router->get('/dashboard', [DashboardController::class, 'index'], 'dashboard');

    // Profile (available to every authenticated user)
    $router->get('/profile', [ProfileController::class, 'edit'], 'profile.edit');
    $router->put('/profile', [ProfileController::class, 'update'], 'profile.update');

    // Students
    // Registered before the resource route so it is not captured by GET /students/{student}.
    $router->get('/students/sample-csv', [StudentController::class, 'sampleCsv'], 'students.sample');
    $router->resource('students', StudentController::class);
    $router->post('/students/import', [StudentController::class, 'import'], 'students.import');

    // Events
    $router->resource('events', EventController::class);

    // Attendance
    $router->get('/attendance', [AttendanceController::class, 'index'], 'attendance.index');
    $router->get('/attendance/event/{eventId}', [AttendanceController::class, 'getEventAttendance'], 'attendance.event');
    $router->post('/attendance/mark', [AttendanceController::class, 'markAttendance'], 'attendance.mark');

    // Reports
    $router->get('/reports', [ReportController::class, 'index'], 'reports.index');
    $router->get('/reports/export/pdf', [ReportController::class, 'exportPdf'], 'reports.pdf');
    $router->get('/reports/export/excel', [ReportController::class, 'exportExcel'], 'reports.excel');

    // Admin-only routes
    $router->group(['middleware' => ['role:admin']], function ($router) {
        $router->resource('users', UserController::class, ['except' => ['show']]);

        $router->get('/audit-logs', [AuditController::class, 'index'], 'audit.index');

        // Settings
        $router->get('/settings', [SystemSettingController::class, 'index'], 'settings.index');
        $router->post('/settings/semester', [SystemSettingController::class, 'updateSemester'], 'settings.semester');
        $router->post('/settings/logo', [SystemSettingController::class, 'updateLogo'], 'settings.logo');

        // Semesters
        $router->post('/semesters', [SemesterController::class, 'store'], 'semesters.store');
        $router->put('/semesters/{id}', [SemesterController::class, 'update'], 'semesters.update');
        $router->delete('/semesters/{id}', [SemesterController::class, 'destroy'], 'semesters.destroy');
        $router->post('/semesters/{id}/activate', [SemesterController::class, 'setActive'], 'semesters.activate');

        // Departments
        $router->post('/departments', [DepartmentController::class, 'store'], 'departments.store');
        $router->put('/departments/{id}', [DepartmentController::class, 'update'], 'departments.update');
        $router->delete('/departments/{id}', [DepartmentController::class, 'destroy'], 'departments.destroy');

        // Programs
        $router->post('/programs', [ProgramController::class, 'store'], 'programs.store');
        $router->put('/programs/{id}', [ProgramController::class, 'update'], 'programs.update');
        $router->delete('/programs/{id}', [ProgramController::class, 'destroy'], 'programs.destroy');
    });
});
