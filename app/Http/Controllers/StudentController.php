<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Student;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['department', 'program']);

        if ($search = $request->input('search')) {
            $query->search($search);
        }

        if ($departmentId = $request->input('department_id')) {
            $query->byDepartment($departmentId);
        }

        if ($level = $request->input('level')) {
            $query->byLevel($level);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $students = $query->orderBy('last_name')->paginate(50, (int) $request->input('page', 1));

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'students'   => $students->items(),
                'pagination' => [
                    'total'       => $students->total(),
                    'page'        => $students->currentPage(),
                    'limit'       => $students->perPage(),
                    'total_pages' => $students->lastPage(),
                ],
            ]);
        }

        $departments = Department::orderBy('name')->get();
        return view('students.index', compact('students', 'departments'));
    }

    public function create()
    {
        $departments = Department::with('programs')->orderBy('name')->get();
        return view('students.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'    => 'required|string|max:50|unique:students,student_id',
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'photo'         => 'nullable|image|max:2048',
            'program_id'    => 'nullable|exists:programs,id',
            'level'         => 'required|integer|min:100|max:400',
            'department_id' => 'nullable|exists:departments,id',
            'faculty'       => 'nullable|string|max:200',
            'is_active'     => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('students');
        }

        $student = Student::create($validated);

        AuditLog::create([
            'action'       => 'CREATE_STUDENT',
            'performed_by' => Auth::id(),
            'target_type'  => 'student',
            'target_id'    => $student->id,
            'metadata'     => ['student_id' => $student->student_id],
            'timestamp'    => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Student created successfully.',
                'student' => $student,
            ], 201);
        }

        return redirect()->route('students.index')->with('success', 'Student created successfully.');
    }

    public function show($id)
    {
        $student = Student::with(['department', 'program', 'attendances.event'])->findOrFail($id);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'student' => $student]);
        }

        return view('students.show', compact('student'));
    }

    public function edit($id)
    {
        $student = Student::findOrFail($id);
        $departments = Department::with('programs')->orderBy('name')->get();
        return view('students.edit', compact('student', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'student_id'    => 'required|string|max:50|unique:students,student_id,' . $id,
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'photo'         => 'nullable|image|max:2048',
            'program_id'    => 'nullable|exists:programs,id',
            'level'         => 'required|integer|min:100|max:400',
            'department_id' => 'nullable|exists:departments,id',
            'faculty'       => 'nullable|string|max:200',
            'is_active'     => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            if ($student->photo) {
                $this->deletePhoto($student->photo);
            }
            $validated['photo'] = $request->file('photo')->store('students');
        }

        $student->update($validated);

        AuditLog::create([
            'action'       => 'UPDATE_STUDENT',
            'performed_by' => Auth::id(),
            'target_type'  => 'student',
            'target_id'    => $student->id,
            'metadata'     => ['student_id' => $student->student_id],
            'timestamp'    => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully.',
                'student' => $student,
            ]);
        }

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        if ($student->photo) {
            $this->deletePhoto($student->photo);
        }

        AuditLog::create([
            'action'       => 'DELETE_STUDENT',
            'performed_by' => Auth::id(),
            'target_type'  => 'student',
            'target_id'    => $id,
            'metadata'     => ['student_id' => $student->student_id],
            'timestamp'    => now(),
        ]);

        $student->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Student deleted successfully.']);
        }

        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }

    /**
     * Stream the import template as a download. Generated in memory so it works
     * on any host regardless of how static files under public/ are served.
     */
    public function sampleCsv()
    {
        $rows = [
            ['student_id', 'first_name', 'last_name', 'level', 'faculty', 'department_id', 'program_id'],
            ['VVU2024001', 'Ama', 'Mensah', '100', '', '1', '1'],
            ['VVU2024002', 'Kwame', 'Osei', '200', '', '1', '2'],
            ['VVU2024003', 'Efua', 'Boateng', '300', '', '2', '3'],
            ['VVU2024004', 'Yaw', 'Owusu', '400', '', '2', '4'],
        ];

        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response()->download($csv, 'students-sample.csv', 'text/csv; charset=UTF-8');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('file')->getRealPath();
        $imported = 0;
        $errors = [];

        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle);
            $header = $header ? array_map('trim', $header) : [];
            $rowNumber = 1;

            while (($data = fgetcsv($handle)) !== false) {
                $rowNumber++;
                if (count(array_filter($data, fn ($v) => $v !== null && $v !== '')) === 0) {
                    continue;
                }
                $record = array_combine($header, array_pad($data, count($header), null));

                try {
                    Student::create([
                        'student_id'    => $record['student_id'] ?? null,
                        'first_name'    => $record['first_name'] ?? null,
                        'last_name'     => $record['last_name'] ?? null,
                        'level'         => (int) ($record['level'] ?? 0),
                        'faculty'       => $record['faculty'] ?? null,
                        'department_id' => $record['department_id'] ?? null,
                        'program_id'    => $record['program_id'] ?? null,
                        'is_active'     => true,
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }
            fclose($handle);
        }

        AuditLog::create([
            'action'       => 'IMPORT_STUDENTS',
            'performed_by' => Auth::id(),
            'target_type'  => 'student',
            'metadata'     => ['imported' => $imported, 'errors' => count($errors)],
            'timestamp'    => now(),
        ]);

        return response()->json([
            'success'  => true,
            'message'  => "Imported {$imported} students.",
            'imported' => $imported,
            'errors'   => $errors,
        ]);
    }

    private function deletePhoto(string $relativePath): void
    {
        // app/Http/Controllers -> project root -> public/uploads
        $file = dirname(__DIR__, 3) . '/public/uploads/' . ltrim($relativePath, '/');
        if (is_file($file)) {
            @unlink($file);
        }
    }
}
