<?php

namespace App\Http\Controllers;

use App\Core\Request;
use App\Exports\AttendanceExport;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Event;
use App\Models\Semester;
use App\Models\Student;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportController extends Controller
{
    public function index()
    {
        $events = Event::orderBy('start_time', 'desc')->limit(50)->get();
        $departments = Department::orderBy('name')->get();
        $semesters = Semester::orderBy('start_date', 'desc')->get();
        $activeSemester = Semester::active()->first();

        return view('reports.index', compact('events', 'departments', 'semesters', 'activeSemester'));
    }

    public function studentReport($studentId)
    {
        $student = Student::with(['department', 'program'])->findOrFail($studentId);

        $attendances = Attendance::where('student_id', $studentId)
            ->with(['event', 'officer'])
            ->orderBy('timestamp', 'desc')
            ->get();

        $totalEvents = Event::count();
        $attendedEvents = $attendances->count();
        $attendanceRate = $totalEvents > 0 ? ($attendedEvents / $totalEvents) * 100 : 0;

        return response()->json([
            'success'         => true,
            'student'         => $student,
            'attendances'     => $attendances,
            'total_events'    => $totalEvents,
            'attended_events' => $attendedEvents,
            'attendance_rate' => round($attendanceRate, 2),
        ]);
    }

    public function eventReport($eventId)
    {
        $event = Event::with(['creator', 'officers'])->findOrFail($eventId);

        $attendances = Attendance::where('event_id', $eventId)
            ->with(['student.department', 'officer'])
            ->orderBy('timestamp', 'desc')
            ->get();

        $totalStudents = Student::active()->count();
        $attendedStudents = $attendances->count();
        $attendanceRate = $totalStudents > 0 ? ($attendedStudents / $totalStudents) * 100 : 0;

        $byDepartment = $attendances->groupBy(function ($attendance) {
            return $attendance->student?->department?->name ?? 'Unknown';
        })->map(fn ($group) => $group->count());

        $byMethod = $attendances->groupBy('method')->map(fn ($group) => $group->count());

        return response()->json([
            'success'          => true,
            'event'            => $event,
            'attendances'      => $attendances,
            'total_students'   => $totalStudents,
            'attended_students' => $attendedStudents,
            'attendance_rate'  => round($attendanceRate, 2),
            'by_department'    => $byDepartment,
            'by_method'        => $byMethod,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'event_id'      => 'nullable|exists:events,id',
            'department_id' => 'nullable|exists:departments,id',
            'semester_id'   => 'nullable|exists:semesters,id',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
        ]);

        $attendances = $this->filteredAttendances($request);
        $semesterId = $request->input('semester_id');

        $data = [
            'title'       => 'Attendance Report',
            'date'        => now()->format('Y-m-d H:i:s'),
            'attendances' => $attendances,
            'total'       => $attendances->count(),
            'semester'    => $semesterId ? Semester::find($semesterId) : null,
        ];

        $html = view('reports.pdf', $data);
        $filename = 'attendance-report-' . now()->format('Y-m-d') . '.pdf';

        // Render with dompdf when available, otherwise fall back to printable HTML.
        if (class_exists(Dompdf::class)) {
            $options = new Options();
            $options->set('isRemoteEnabled', false);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('defaultFont', 'Helvetica');
            $options->set('dpi', 96);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response()->download($dompdf->output(), $filename, 'application/pdf');
        }

        return response()->make($html);
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'event_id'      => 'nullable|exists:events,id',
            'department_id' => 'nullable|exists:departments,id',
            'semester_id'   => 'nullable|exists:semesters,id',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
        ]);

        $attendances = $this->filteredAttendances($request);
        $csv = (new AttendanceExport($attendances))->toCsv();
        $filename = 'attendance-report-' . now()->format('Y-m-d') . '.csv';

        return response()->download($csv, $filename, 'text/csv; charset=UTF-8');
    }

    public function departmentReport($departmentId)
    {
        $department = Department::with('programs')->findOrFail($departmentId);

        $students = Student::where('department_id', $departmentId)->where('is_active', true)->get();

        $totalStudents = $students->count();
        $totalAttendance = Attendance::whereIn('student_id', $students->pluck('id'))->count();
        $totalEvents = Event::count();

        $attendanceRate = ($totalStudents > 0 && $totalEvents > 0)
            ? ($totalAttendance / ($totalStudents * $totalEvents)) * 100
            : 0;

        return response()->json([
            'success'          => true,
            'department'       => $department,
            'total_students'   => $totalStudents,
            'total_attendance' => $totalAttendance,
            'attendance_rate'  => round($attendanceRate, 2),
        ]);
    }

    /**
     * Build the filtered attendance collection shared by PDF/Excel exports.
     */
    private function filteredAttendances(Request $request)
    {
        $query = Attendance::with(['student.department', 'event', 'officer']);

        if ($eventId = $request->input('event_id')) {
            $query->where('event_id', $eventId);
        }
        if ($semesterId = $request->input('semester_id')) {
            $query->whereHas('event', fn ($q) => $q->where('semester_id', $semesterId));
        }
        if ($departmentId = $request->input('department_id')) {
            $query->whereHas('student', fn ($q) => $q->where('department_id', $departmentId));
        }
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('timestamp', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $query->whereDate('timestamp', '<=', $endDate);
        }

        return $query->orderBy('timestamp', 'desc')->get();
    }
}
