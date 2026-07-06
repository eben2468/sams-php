<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\EventOfficer;
use App\Models\Semester;
use App\Models\User;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with(['creator', 'officers', 'semester']);

        $semesterId = $request->input('semester_id');
        if ($semesterId === 'all') {
            // Show all events regardless of semester
        } elseif ($semesterId) {
            $query->bySemester($semesterId);
        } else {
            $activeSemester = Semester::active()->first();
            if ($activeSemester) {
                $query->bySemester($activeSemester->id);
            }
        }

        if ($type = $request->input('type')) {
            $query->byType($type);
        }

        $status = $request->input('status', 'all');
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'upcoming') {
            $query->upcoming();
        } elseif ($status === 'past') {
            $query->past();
        }

        $events = $query->orderBy('start_time', 'desc')->paginate(20, (int) $request->input('page', 1));

        $semesters = Semester::orderBy('start_date', 'desc')->get();
        $activeSemester = Semester::active()->first();

        if ($request->expectsJson()) {
            return response()->json([
                'success'         => true,
                'events'          => $events->items(),
                'pagination'      => [
                    'total'       => $events->total(),
                    'page'        => $events->currentPage(),
                    'limit'       => $events->perPage(),
                    'total_pages' => $events->lastPage(),
                ],
                'semesters'       => $semesters,
                'active_semester' => $activeSemester,
            ]);
        }

        return view('events.index', compact('events', 'semesters', 'activeSemester'));
    }

    public function create()
    {
        $officers = User::where('role', 'officer')->where('is_active', true)->orderBy('name')->get();
        $semesters = Semester::orderBy('start_date', 'desc')->get();
        $activeSemester = Semester::active()->first();

        return view('events.create', compact('officers', 'semesters', 'activeSemester'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|in:church_service,special_program,week_of_emphasis,idf',
            'start_time'     => 'required|date',
            'end_time'       => 'required|date|after:start_time',
            'description'    => 'nullable|string',
            'semester_id'    => 'nullable|exists:semesters,id',
            'officer_ids'    => 'nullable|array',
            'officer_ids.*'  => 'exists:users,id',
        ]);

        if (empty($validated['semester_id'])) {
            $activeSemester = Semester::active()->first();
            $validated['semester_id'] = $activeSemester ? $activeSemester->id : null;
        }

        Database::beginTransaction();
        try {
            $event = Event::create([
                'name'        => $validated['name'],
                'type'        => $validated['type'],
                'start_time'  => $validated['start_time'],
                'end_time'    => $validated['end_time'],
                'description' => $validated['description'] ?? null,
                'created_by'  => Auth::id(),
                'semester_id' => $validated['semester_id'],
            ]);

            if (!empty($validated['officer_ids'])) {
                foreach ($validated['officer_ids'] as $officerId) {
                    EventOfficer::create(['event_id' => $event->id, 'user_id' => $officerId]);
                }
            }

            AuditLog::create([
                'action'       => 'CREATE_EVENT',
                'performed_by' => Auth::id(),
                'target_type'  => 'event',
                'target_id'    => $event->id,
                'metadata'     => ['name' => $event->name, 'type' => $event->type, 'semester_id' => $event->semester_id],
                'timestamp'    => now(),
            ]);

            Database::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event created successfully.',
                    'event'   => $event->load('officers', 'semester'),
                ], 201);
            }

            return redirect()->route('events.index')->with('success', 'Event created successfully.');
        } catch (\Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public function show($id)
    {
        $event = Event::with(['creator', 'officers', 'attendances.student'])->findOrFail($id);

        $attendanceCount = $event->attendances()->count();
        $attendancePercentage = $event->getAttendancePercentage();

        if (request()->expectsJson()) {
            return response()->json([
                'success'                => true,
                'event'                  => $event,
                'attendance_count'       => $attendanceCount,
                'attendance_percentage'  => $attendancePercentage,
            ]);
        }

        return view('events.show', compact('event', 'attendanceCount', 'attendancePercentage'));
    }

    public function edit($id)
    {
        $event = Event::with('officers')->findOrFail($id);
        $officers = User::where('role', 'officer')->where('is_active', true)->orderBy('name')->get();
        $semesters = Semester::orderBy('start_date', 'desc')->get();

        return view('events.edit', compact('event', 'officers', 'semesters'));
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|in:church_service,special_program,week_of_emphasis,idf',
            'start_time'     => 'required|date',
            'end_time'       => 'required|date|after:start_time',
            'description'    => 'nullable|string',
            'semester_id'    => 'nullable|exists:semesters,id',
            'officer_ids'    => 'nullable|array',
            'officer_ids.*'  => 'exists:users,id',
        ]);

        Database::beginTransaction();
        try {
            $event->update([
                'name'        => $validated['name'],
                'type'        => $validated['type'],
                'start_time'  => $validated['start_time'],
                'end_time'    => $validated['end_time'],
                'description' => $validated['description'] ?? null,
                'semester_id' => $validated['semester_id'] ?? $event->semester_id,
            ]);

            EventOfficer::where('event_id', $event->id)->delete();
            if (!empty($validated['officer_ids'])) {
                foreach ($validated['officer_ids'] as $officerId) {
                    EventOfficer::create(['event_id' => $event->id, 'user_id' => $officerId]);
                }
            }

            AuditLog::create([
                'action'       => 'UPDATE_EVENT',
                'performed_by' => Auth::id(),
                'target_type'  => 'event',
                'target_id'    => $event->id,
                'metadata'     => ['name' => $event->name, 'semester_id' => $event->semester_id],
                'timestamp'    => now(),
            ]);

            Database::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event updated successfully.',
                    'event'   => $event->load('officers', 'semester'),
                ]);
            }

            return redirect()->route('events.index')->with('success', 'Event updated successfully.');
        } catch (\Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);

        AuditLog::create([
            'action'       => 'DELETE_EVENT',
            'performed_by' => Auth::id(),
            'target_type'  => 'event',
            'target_id'    => $id,
            'metadata'     => ['name' => $event->name],
            'timestamp'    => now(),
        ]);

        $event->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Event deleted successfully.']);
        }

        return redirect()->route('events.index')->with('success', 'Event deleted successfully.');
    }

    public function getActive()
    {
        $events = Event::active()->with(['creator', 'officers'])->get();

        return response()->json(['success' => true, 'events' => $events]);
    }
}
