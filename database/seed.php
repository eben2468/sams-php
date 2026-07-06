<?php

/*
| Database seeder. Inserts demo users, departments, programs, students,
| semesters and events. Replaces Laravel's DatabaseSeeder + SemesterSeeder.
|
| Run directly:  php database/seed.php
*/

use App\Core\Database;
use App\Core\Hash;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Event;
use App\Models\EventOfficer;
use App\Models\Program;
use App\Models\Semester;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;

if (!function_exists('sams_seed')) {
    function sams_seed(): void
    {
        // Clear existing data (idempotent re-seed).
        Database::statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'attendance', 'event_officers', 'audit_logs', 'events', 'students',
            'programs', 'departments', 'semesters', 'system_settings', 'users',
        ] as $table) {
            Database::statement("TRUNCATE TABLE `{$table}`");
        }
        Database::statement('SET FOREIGN_KEY_CHECKS = 1');

        // --- Users -------------------------------------------------------
        $admin = User::create([
            'name' => 'Admin User', 'email' => 'admin@vvu.edu.gh',
            'password' => Hash::make('admin123'), 'role' => 'admin', 'is_active' => true,
        ]);
        $officer1 = User::create([
            'name' => 'Officer One', 'email' => 'officer1@vvu.edu.gh',
            'password' => Hash::make('officer123'), 'role' => 'officer', 'is_active' => true,
        ]);
        $officer2 = User::create([
            'name' => 'Officer Two', 'email' => 'officer2@vvu.edu.gh',
            'password' => Hash::make('officer123'), 'role' => 'officer', 'is_active' => true,
        ]);
        User::create([
            'name' => 'Supervisor User', 'email' => 'supervisor@vvu.edu.gh',
            'password' => Hash::make('supervisor123'), 'role' => 'supervisor', 'is_active' => true,
        ]);

        // --- Departments -------------------------------------------------
        $departments = [];
        foreach (['Computer Science', 'Business Administration', 'Theology', 'Nursing', 'Education'] as $name) {
            $departments[] = Department::create(['name' => $name]);
        }

        // --- Programs ----------------------------------------------------
        $programs = [];
        foreach ([
            ['name' => 'BSc Computer Science', 'department_id' => $departments[0]->id],
            ['name' => 'BSc Information Technology', 'department_id' => $departments[0]->id],
            ['name' => 'BBA Management', 'department_id' => $departments[1]->id],
            ['name' => 'BBA Accounting', 'department_id' => $departments[1]->id],
            ['name' => 'BA Theology', 'department_id' => $departments[2]->id],
            ['name' => 'BSc Nursing', 'department_id' => $departments[3]->id],
            ['name' => 'BEd Primary Education', 'department_id' => $departments[4]->id],
        ] as $program) {
            $programs[] = Program::create($program);
        }

        // --- Students ----------------------------------------------------
        $students = [
            ['student_id' => 'VVU2024001', 'first_name' => 'John', 'last_name' => 'Mensah', 'level' => 100, 'faculty' => 'Science & Technology', 'program_id' => $programs[0]->id, 'department_id' => $departments[0]->id],
            ['student_id' => 'VVU2024002', 'first_name' => 'Mary', 'last_name' => 'Asante', 'level' => 200, 'faculty' => 'Science & Technology', 'program_id' => $programs[1]->id, 'department_id' => $departments[0]->id],
            ['student_id' => 'VVU2024003', 'first_name' => 'Kwame', 'last_name' => 'Boateng', 'level' => 300, 'faculty' => 'Business', 'program_id' => $programs[2]->id, 'department_id' => $departments[1]->id],
            ['student_id' => 'VVU2024004', 'first_name' => 'Ama', 'last_name' => 'Owusu', 'level' => 100, 'faculty' => 'Business', 'program_id' => $programs[3]->id, 'department_id' => $departments[1]->id],
            ['student_id' => 'VVU2024005', 'first_name' => 'Kofi', 'last_name' => 'Adjei', 'level' => 200, 'faculty' => 'Theology', 'program_id' => $programs[4]->id, 'department_id' => $departments[2]->id],
            ['student_id' => 'VVU2024006', 'first_name' => 'Abena', 'last_name' => 'Sarpong', 'level' => 100, 'faculty' => 'Health Sciences', 'program_id' => $programs[5]->id, 'department_id' => $departments[3]->id],
            ['student_id' => 'VVU2024007', 'first_name' => 'Yaw', 'last_name' => 'Osei', 'level' => 300, 'faculty' => 'Education', 'program_id' => $programs[6]->id, 'department_id' => $departments[4]->id],
            ['student_id' => 'VVU2024008', 'first_name' => 'Akua', 'last_name' => 'Frimpong', 'level' => 200, 'faculty' => 'Science & Technology', 'program_id' => $programs[0]->id, 'department_id' => $departments[0]->id],
            ['student_id' => 'VVU2024009', 'first_name' => 'Kwabena', 'last_name' => 'Appiah', 'level' => 100, 'faculty' => 'Business', 'program_id' => $programs[2]->id, 'department_id' => $departments[1]->id],
            ['student_id' => 'VVU2024010', 'first_name' => 'Efua', 'last_name' => 'Agyeman', 'level' => 400, 'faculty' => 'Theology', 'program_id' => $programs[4]->id, 'department_id' => $departments[2]->id],
        ];
        foreach ($students as $student) {
            $student['is_active'] = true;
            Student::create($student);
        }

        // --- Semesters ---------------------------------------------------
        $fall = Semester::create([
            'name' => 'Fall 2026', 'description' => 'Academic Year 2026-2027 Fall Semester',
            'start_date' => '2026-08-15', 'end_date' => '2026-12-20', 'is_active' => true,
        ]);
        Semester::create([
            'name' => 'Spring 2027', 'description' => 'Academic Year 2026-2027 Spring Semester',
            'start_date' => '2027-01-10', 'end_date' => '2027-05-15', 'is_active' => false,
        ]);
        Semester::create([
            'name' => 'Summer 2027', 'description' => 'Academic Year 2026-2027 Summer Semester',
            'start_date' => '2027-06-01', 'end_date' => '2027-08-10', 'is_active' => false,
        ]);

        // --- Events ------------------------------------------------------
        $event1 = Event::create([
            'name' => 'Sunday Morning Service', 'type' => 'church_service',
            'start_time' => Carbon::now()->subDays(7)->setTime(9, 0)->toDateTimeString(),
            'end_time' => Carbon::now()->subDays(7)->setTime(11, 0)->toDateTimeString(),
            'description' => 'Regular Sunday morning worship service',
            'created_by' => $admin->id, 'semester_id' => $fall->id,
        ]);
        $event2 = Event::create([
            'name' => 'Week of Spiritual Emphasis', 'type' => 'week_of_emphasis',
            'start_time' => Carbon::now()->subDays(3)->setTime(18, 0)->toDateTimeString(),
            'end_time' => Carbon::now()->subDays(3)->setTime(20, 0)->toDateTimeString(),
            'description' => 'Special week of spiritual emphasis',
            'created_by' => $admin->id, 'semester_id' => $fall->id,
        ]);
        $event3 = Event::create([
            'name' => 'Chapel Service', 'type' => 'church_service',
            'start_time' => Carbon::now()->addDays(2)->setTime(10, 0)->toDateTimeString(),
            'end_time' => Carbon::now()->addDays(2)->setTime(11, 30)->toDateTimeString(),
            'description' => 'Midweek chapel service',
            'created_by' => $admin->id, 'semester_id' => $fall->id,
        ]);

        EventOfficer::create(['event_id' => $event1->id, 'user_id' => $officer1->id]);
        EventOfficer::create(['event_id' => $event1->id, 'user_id' => $officer2->id]);
        EventOfficer::create(['event_id' => $event2->id, 'user_id' => $officer1->id]);
        EventOfficer::create(['event_id' => $event3->id, 'user_id' => $officer2->id]);

        // --- System settings ---------------------------------------------
        SystemSetting::create(['key' => 'app_name', 'value' => 'SAMS - Student Attendance Management System']);
        SystemSetting::create(['key' => 'institution_name', 'value' => 'Valley View University']);
        SystemSetting::create(['key' => 'active_semester', 'value' => 'Semester 1 (Fall 2026)']);
        SystemSetting::create(['key' => 'attendance_grace_period', 'value' => '15']);
        SystemSetting::create(['key' => 'require_photo', 'value' => 'false']);
    }
}

// When executed directly from the CLI, bootstrap and run.
if (PHP_SAPI === 'cli' && isset($argv) && realpath($argv[0]) === realpath(__FILE__)) {
    require __DIR__ . '/_bootstrap.php';
    sams_seed();
    echo "Database seeded successfully!\n";
    echo "Login credentials:\n";
    echo "  Admin:      admin@vvu.edu.gh / admin123\n";
    echo "  Officer:    officer1@vvu.edu.gh / officer123\n";
    echo "  Supervisor: supervisor@vvu.edu.gh / supervisor123\n";
}
