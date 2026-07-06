<?php

/**
 * API routes (JSON). Prefixed with /api and CSRF-exempt (session-authenticated,
 * same-origin JSON). $router is provided by public/index.php.
 *
 * @var \App\Core\Router $router
 */

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\UserController;

$router->group(['prefix' => '/api', 'csrf' => false], function ($router) {

    // Public
    $router->post('/auth/login', [AuthController::class, 'login']);

    $router->get('/health', function () {
        return response()->json([
            'status'    => 'ok',
            'timestamp' => \Carbon\Carbon::now()->toIso8601String(),
        ]);
    });

    // Protected (session auth)
    $router->group(['middleware' => ['auth']], function ($router) {
        $router->post('/auth/logout', [AuthController::class, 'logout']);
        $router->get('/auth/me', [AuthController::class, 'me']);

        $router->get('/dashboard/stats', [DashboardController::class, 'index']);

        // Students
        $router->get('/students', [StudentController::class, 'index']);
        $router->post('/students', [StudentController::class, 'store'], null, ['role:admin']);
        $router->get('/students/{id}', [StudentController::class, 'show']);
        $router->put('/students/{id}', [StudentController::class, 'update'], null, ['role:admin']);
        $router->delete('/students/{id}', [StudentController::class, 'destroy'], null, ['role:admin']);
        $router->post('/students/import', [StudentController::class, 'import'], null, ['role:admin']);

        // Events
        $router->get('/events', [EventController::class, 'index']);
        $router->post('/events', [EventController::class, 'store'], null, ['role:admin']);
        $router->get('/events/active', [EventController::class, 'getActive']);
        $router->get('/events/{id}', [EventController::class, 'show']);
        $router->put('/events/{id}', [EventController::class, 'update'], null, ['role:admin']);
        $router->delete('/events/{id}', [EventController::class, 'destroy'], null, ['role:admin']);

        // Attendance
        $router->post('/attendance/mark', [AttendanceController::class, 'markAttendance'], null, ['role:admin,officer']);
        $router->get('/attendance/event/{eventId}', [AttendanceController::class, 'getEventAttendance']);
        $router->get('/attendance/student/{studentId}', [AttendanceController::class, 'getStudentAttendance']);
        $router->get('/attendance/absentees/{eventId}', [AttendanceController::class, 'getAbsentees']);
        $router->delete('/attendance/{id}', [AttendanceController::class, 'destroy'], null, ['role:admin']);

        // Reports
        $router->get('/reports/student/{studentId}', [ReportController::class, 'studentReport'], null, ['role:admin,supervisor']);
        $router->get('/reports/event/{eventId}', [ReportController::class, 'eventReport'], null, ['role:admin,supervisor']);
        $router->get('/reports/department/{departmentId}', [ReportController::class, 'departmentReport'], null, ['role:admin,supervisor']);
        $router->get('/reports/export/pdf', [ReportController::class, 'exportPdf'], null, ['role:admin,supervisor']);
        $router->get('/reports/export/excel', [ReportController::class, 'exportExcel'], null, ['role:admin,supervisor']);

        // Users
        $router->get('/users', [UserController::class, 'index'], null, ['role:admin']);
        $router->post('/users', [UserController::class, 'store'], null, ['role:admin']);
        $router->put('/users/{id}', [UserController::class, 'update'], null, ['role:admin']);
        $router->delete('/users/{id}', [UserController::class, 'destroy'], null, ['role:admin']);

        // Audit Logs
        $router->get('/audit-logs', [AuditController::class, 'index'], null, ['role:admin']);

        // Departments
        $router->get('/departments', [DepartmentController::class, 'index']);
        $router->post('/departments', [DepartmentController::class, 'store'], null, ['role:admin']);
        $router->put('/departments/{id}', [DepartmentController::class, 'update'], null, ['role:admin']);
        $router->delete('/departments/{id}', [DepartmentController::class, 'destroy'], null, ['role:admin']);

        // Programs
        $router->get('/programs', [ProgramController::class, 'index']);
        $router->post('/programs', [ProgramController::class, 'store'], null, ['role:admin']);
        $router->put('/programs/{id}', [ProgramController::class, 'update'], null, ['role:admin']);
        $router->delete('/programs/{id}', [ProgramController::class, 'destroy'], null, ['role:admin']);

        // System Settings
        $router->get('/settings', [SystemSettingController::class, 'index'], null, ['role:admin']);
        $router->put('/settings', [SystemSettingController::class, 'update'], null, ['role:admin']);
    });
});
