<?php layout('layouts.app'); ?>
<?php section('title', 'Students - SAMS'); ?>
<?php section('page-title', 'Students Management'); ?>
<?php section('page-subtitle', 'Manage and view all student records'); ?>

<?php section('content'); ?>
<div class="space-y-6 animate-fade-in max-w-full">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Student Directory</h1>
            <p class="text-sm text-gray-600 mt-1">Total: <?= e($students->total()) ?> students</p>
        </div>
        <?php if (auth()->user()->isAdmin()): ?>
        <div class="flex flex-wrap gap-3">
            <button onclick="document.getElementById('importModal').classList.remove('hidden')"
                    class="btn-ripple px-5 py-2.5 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 shadow-medium hover:shadow-strong transition-all duration-200 font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <span>Import CSV</span>
            </button>
            <a href="<?= route('students.create') ?>"
               class="btn-ripple px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl hover:from-indigo-600 hover:to-purple-700 shadow-medium hover:shadow-strong transition-all duration-200 font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Add Student</span>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl shadow-soft hover:shadow-medium transition-all duration-300 overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-slate-50 to-gray-50 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter Students
            </h3>
        </div>
        <form method="GET" action="<?= route('students.index') ?>" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" id="search" name="search" value="<?= e(request('search')) ?>"
                               placeholder="Student ID or Name"
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <div>
                    <label for="department_id" class="block text-sm font-semibold text-gray-700 mb-2">Department</label>
                    <select id="department_id" name="department_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?= e($dept->id) ?>" <?= request('department_id') == $dept->id ? 'selected' : '' ?>><?= e($dept->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="level" class="block text-sm font-semibold text-gray-700 mb-2">Level</label>
                    <select id="level" name="level" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <option value="">All Levels</option>
                        <?php foreach ([100, 200, 300, 400] as $lvl): ?>
                        <option value="<?= $lvl ?>" <?= request('level') == (string) $lvl ? 'selected' : '' ?>><?= $lvl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full btn-ripple bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-2.5 rounded-xl hover:from-indigo-600 hover:to-purple-700 shadow-medium hover:shadow-strong transition-all duration-200 font-medium">Apply Filters</button>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-soft hover:shadow-medium transition-all duration-300 overflow-hidden max-w-full">
        <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
                <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-slate-50 to-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Student ID</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider min-w-[200px]">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Department</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Program</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Level</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Status</th>
                        <?php if (auth()->user()->isAdmin()): ?>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php $__empty = true; foreach ($students as $student): $__empty = false; ?>
                    <tr class="table-row-hover">
                        <td class="px-4 py-3 whitespace-nowrap"><span class="text-sm font-bold text-indigo-600"><?= e($student->student_id) ?></span></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center min-w-[200px]">
                                <div class="flex-shrink-0 w-10 h-10 rounded-lg overflow-hidden shadow-soft">
                                    <?php if ($student->photo): ?>
                                        <img src="<?= e(media($student->photo)) ?>" alt="<?= e($student->full_name) ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold"><?= e(substr($student->full_name, 0, 1)) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-gray-900"><?= e($student->full_name) ?></div>
                                    <div class="text-xs text-gray-500"><?= e($student->faculty) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap"><span class="text-sm text-gray-700 font-medium"><?= e($student->department?->name ?? '-') ?></span></td>
                        <td class="px-4 py-3 whitespace-nowrap"><span class="text-sm text-gray-700"><?= e($student->program?->name ?? '-') ?></span></td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2.5 py-1 text-xs font-bold bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 rounded-lg shadow-soft">Level <?= e($student->level) ?></span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg shadow-soft <?= $student->is_active ? 'bg-gradient-to-r from-green-400 to-green-500 text-white' : 'bg-gradient-to-r from-red-400 to-red-500 text-white' ?>"><?= $student->is_active ? 'Active' : 'Inactive' ?></span>
                        </td>
                        <?php if (auth()->user()->isAdmin()): ?>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="<?= route('students.edit', $student->id) ?>" class="text-indigo-600 hover:text-indigo-800 font-semibold transition-colors flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                                <form action="<?= route('students.destroy', $student->id) ?>" method="POST" class="inline">
                                    <?= csrf_field() ?>
                                    <?= method_field('DELETE') ?>
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this student?')" class="text-red-600 hover:text-red-800 font-semibold transition-colors flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; if ($__empty): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <p class="mt-4 text-lg font-medium text-gray-500">No students found.</p>
                            <p class="mt-2 text-sm text-gray-400">Try adjusting your filters or add a new student.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>

        <?php if ($students->hasPages()): ?>
        <div class="px-6 py-4 border-t border-gray-100 bg-gradient-to-r from-slate-50 to-gray-50">
            <?= $students->links() ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in">
    <div class="bg-white rounded-2xl shadow-strong max-w-md w-full mx-4 animate-scale-in">
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-green-600 shadow-medium">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Import Students</h3>
                        <p class="text-sm text-gray-600">Upload CSV file</p>
                    </div>
                </div>
                <button onclick="document.getElementById('importModal').classList.add('hidden')" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <form action="<?= route('students.import') ?>" method="POST" enctype="multipart/form-data" class="p-6">
            <?= csrf_field() ?>
            <div class="mb-6">
                <label for="file" class="block text-sm font-semibold text-gray-700 mb-3">CSV File</label>
                <div class="relative">
                    <input type="file" id="file" name="file" accept=".csv" required class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all hover:border-green-400">
                </div>
                <div class="mt-3 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <p class="text-xs font-semibold text-blue-900 mb-2">Required CSV Columns:</p>
                    <p class="text-xs text-blue-700 leading-relaxed">student_id, first_name, last_name, level, faculty, department_id, program_id</p>
                    <a href="<?= e(route('students.sample')) ?>" download class="mt-3 inline-flex items-center space-x-1.5 text-xs font-semibold text-blue-700 hover:text-blue-900 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Download sample CSV</span>
                    </a>
                </div>
            </div>

            <div class="flex space-x-3">
                <button type="submit" class="flex-1 btn-ripple bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-xl hover:from-green-600 hover:to-green-700 shadow-medium hover:shadow-strong transition-all duration-200 font-semibold">Import Students</button>
                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 px-6 py-3 rounded-xl hover:bg-gray-200 transition-all duration-200 font-semibold">Cancel</button>
            </div>
        </form>
    </div>
</div>
<?php endsection(); ?>
