<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Student;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $activeEvents = Event::active()->with(['creator', 'semester'])->get();

        $selectedEvent = null;
        $attendances = null;
        $totalStudents = 0;
        $attendanceCount = 0;

        if ($request->has('event') && $request->event) {
            $selectedEvent = Event::with(['semester', 'officers'])->find($request->event);

            if ($selectedEvent) {
                $page = (int) $request->input('page', 1);
                $limit = (int) $request->input('limit', 50);

                $attendances = Attendance::where('event_id', $selectedEvent->id)
                    ->with(['student.program', 'student.department', 'officer'])
                    ->orderBy('timestamp', 'desc')
                    ->paginate($limit, $page);

                $totalStudents = Student::active()->count();
                $attendanceCount = Attendance::where('event_id', $selectedEvent->id)->count();
            }
        }

        return view('attendance.index', compact('activeEvents', 'selectedEvent', 'attendances', 'totalStudents', 'attendanceCount'));
    }

    public function markAttendance(Request $request)
    {
        $request->validate([
            'event_id'   => 'required|exists:events,id',
            'student_id' => 'required|string',
            'method'     => 'nullable|in:scan,manual,ocr_scan',
        ]);

        $method = $request->input('method', 'scan');

        $student = Student::where('student_id', $request->student_id)
            ->with(['department', 'program'])
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'status'  => 'INVALID_ID',
                'message' => 'Student not found.',
            ], 404);
        }

        if (!$student->is_active) {
            return response()->json([
                'success' => false,
                'status'  => 'INVALID_ID',
                'message' => 'Student account is inactive.',
            ], 400);
        }

        $existing = Attendance::where('event_id', $request->event_id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'status'  => 'ALREADY_RECORDED',
                'message' => 'Attendance already recorded for this student.',
                'student' => $this->studentPayload($student),
            ], 409);
        }

        $attendance = Attendance::create([
            'event_id'    => $request->event_id,
            'student_id'  => $student->id,
            'marked_by'   => Auth::id(),
            'method'      => $method,
            'is_verified' => in_array($method, ['scan', 'ocr_scan'], true),
            'timestamp'   => now(),
        ]);

        AuditLog::create([
            'action'       => 'MARK_ATTENDANCE',
            'performed_by' => Auth::id(),
            'target_type'  => 'attendance',
            'target_id'    => $attendance->id,
            'metadata'     => [
                'student_id' => $student->student_id,
                'event_id'   => $request->event_id,
                'method'     => $method,
            ],
            'timestamp'    => now(),
        ]);

        $totalMarked = Attendance::where('event_id', $request->event_id)->count();

        return response()->json([
            'success'      => true,
            'status'       => 'PRESENT',
            'message'      => 'Attendance marked successfully.',
            'student'      => $this->studentPayload($student),
            'attendance'   => $attendance,
            'total_marked' => $totalMarked,
        ], 201);
    }

    public function getEventAttendance(Request $request, $eventId)
    {
        $event = Event::with(['semester', 'officers'])->findOrFail($eventId);
        $page = (int) $request->input('page', 1);
        $limit = (int) $request->input('limit', 50);

        $attendances = Attendance::where('event_id', $eventId)
            ->with(['student.program', 'student.department', 'officer'])
            ->orderBy('timestamp', 'desc')
            ->paginate($limit, $page);

        $totalStudents = Student::active()->count();
        $attendanceCount = Attendance::where('event_id', $eventId)->count();

        return response()->json([
            'success'     => true,
            'attendances' => $attendances->items(),
            'pagination'  => [
                'total'       => $attendances->total(),
                'page'        => $attendances->currentPage(),
                'limit'       => $attendances->perPage(),
                'total_pages' => $attendances->lastPage(),
            ],
            'stats' => [
                'total_students'  => $totalStudents,
                'attendance_count' => $attendanceCount,
                'attendance_rate' => $totalStudents > 0 ? round(($attendanceCount / $totalStudents) * 100, 1) : 0,
            ],
        ]);
    }

    public function getStudentAttendance($studentId)
    {
        $attendances = Attendance::where('student_id', $studentId)
            ->with(['event', 'officer'])
            ->orderBy('timestamp', 'desc')
            ->get();

        return response()->json(['success' => true, 'attendances' => $attendances]);
    }

    public function getAbsentees($eventId)
    {
        $presentStudentIds = Attendance::where('event_id', $eventId)->pluck('student_id')->all();

        $absentees = Student::active()
            ->whereNotIn('id', $presentStudentIds)
            ->orderBy('last_name', 'asc')
            ->get();

        return response()->json([
            'success'      => true,
            'absentees'    => $absentees,
            'total_absent' => $absentees->count(),
        ]);
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);

        AuditLog::create([
            'action'       => 'DELETE_ATTENDANCE',
            'performed_by' => Auth::id(),
            'target_type'  => 'attendance',
            'target_id'    => $id,
            'metadata'     => [
                'event_id'   => $attendance->event_id,
                'student_id' => $attendance->student_id,
            ],
            'timestamp'    => now(),
        ]);

        $attendance->delete();

        return response()->json(['success' => true, 'message' => 'Attendance record deleted.']);
    }

    private function studentPayload(Student $student): array
    {
        return [
            'id'         => $student->id,
            'student_id' => $student->student_id,
            'first_name' => $student->first_name,
            'last_name'  => $student->last_name,
            'photo'      => $student->photo,
            'program'    => $student->program?->name,
            'level'      => $student->level,
            'department' => $student->department?->name ?? '-',
        ];
    }
}
