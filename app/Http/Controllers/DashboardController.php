<?php

namespace App\Http\Controllers;

use App\Core\Collection;
use App\Core\Database;
use App\Core\Request;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = $this->getStats();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'stats'   => $stats,
            ]);
        }

        return view('dashboard.index', compact('stats'));
    }

    private function getStats(): array
    {
        // Basic counts
        $totalStudents = Student::active()->count();
        $totalEvents = Event::count();
        $totalAttendance = Attendance::count();
        $totalUsers = User::where('is_active', true)->count();

        $activeEvents = Event::active()->count();

        $recentEvents = Event::where('start_time', '>=', Carbon::now()->subDays(30)->toDateTimeString())->count();

        $avgAttendanceRate = 0;
        if ($totalEvents > 0 && $totalStudents > 0) {
            $avgAttendanceRate = ($totalAttendance / ($totalEvents * $totalStudents)) * 100;
        }

        // Attendance by event type
        $attendanceByType = [];
        foreach (Database::select(
            'SELECT e.type AS type, COUNT(*) AS count
             FROM attendance a JOIN events e ON a.event_id = e.id
             GROUP BY e.type'
        ) as $row) {
            $attendanceByType[$row['type']] = (int) $row['count'];
        }

        // Attendance by department (top 10)
        $attendanceByDepartment = new Collection(array_map(
            fn ($row) => ['name' => $row['name'], 'count' => (int) $row['count']],
            Database::select(
                'SELECT d.name AS name, COUNT(*) AS count
                 FROM attendance a
                 JOIN students s ON a.student_id = s.id
                 JOIN departments d ON s.department_id = d.id
                 GROUP BY d.id, d.name
                 ORDER BY count DESC
                 LIMIT 10'
            )
        ));

        // Recent attendance trend (last 7 days)
        $attendanceTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = (int) Database::scalar(
                'SELECT COUNT(*) FROM attendance WHERE DATE(`timestamp`) = ?',
                [$date->toDateString()]
            );
            $attendanceTrend[] = ['date' => $date->format('Y-m-d'), 'count' => $count];
        }

        // Top attended events (last 30 days)
        $topEvents = Event::withCount('attendances')
            ->where('start_time', '>=', Carbon::now()->subDays(30)->toDateTimeString())
            ->orderBy('attendances_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($event) use ($totalStudents) {
                return [
                    'id'                    => $event->id,
                    'name'                  => $event->name,
                    'type'                  => $event->type,
                    'start_time'            => $event->start_time,
                    'attendance_count'      => (int) $event->attendances_count,
                    'attendance_percentage' => $totalStudents > 0
                        ? round(((int) $event->attendances_count / $totalStudents) * 100, 2)
                        : 0,
                ];
            });

        // Students by level
        $studentsByLevel = [];
        foreach (Database::select(
            'SELECT level, COUNT(*) AS count FROM students WHERE is_active = 1 GROUP BY level ORDER BY level'
        ) as $row) {
            $studentsByLevel[$row['level']] = (int) $row['count'];
        }

        // Attendance methods distribution
        $attendanceByMethod = [];
        foreach (Database::select(
            'SELECT method, COUNT(*) AS count FROM attendance GROUP BY method'
        ) as $row) {
            $attendanceByMethod[$row['method']] = (int) $row['count'];
        }

        // Recent activities (last 20)
        $recentActivities = Attendance::with(['student', 'event', 'officer'])
            ->orderBy('timestamp', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($attendance) {
                return [
                    'id'           => $attendance->id,
                    'student_name' => $attendance->student?->full_name,
                    'student_id'   => $attendance->student?->student_id,
                    'event_name'   => $attendance->event?->name,
                    'officer_name' => $attendance->officer?->name,
                    'method'       => $attendance->method,
                    'timestamp'    => $attendance->timestamp,
                ];
            });

        return [
            'total_students'           => $totalStudents,
            'total_events'             => $totalEvents,
            'total_attendance'         => $totalAttendance,
            'total_users'              => $totalUsers,
            'active_events'            => $activeEvents,
            'recent_events'            => $recentEvents,
            'avg_attendance_rate'      => round($avgAttendanceRate, 2),
            'attendance_by_type'       => $attendanceByType,
            'attendance_by_department' => $attendanceByDepartment,
            'attendance_trend'         => $attendanceTrend,
            'top_events'               => $topEvents,
            'students_by_level'        => $studentsByLevel,
            'attendance_by_method'     => $attendanceByMethod,
            'recent_activities'        => $recentActivities,
        ];
    }
}
