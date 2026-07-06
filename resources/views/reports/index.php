<?php

use App\Models\Attendance;
use App\Models\Event;

layout('layouts.app');
?>
<?php section('title', 'Reports - SAMS'); ?>

<?php section('content'); ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Export Attendance Reports</h2>

        <form method="GET" id="reportForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label for="semester_id" class="block text-sm font-medium text-gray-700 mb-2">Semester</label>
                    <select id="semester_id" name="semester_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Semesters</option>
                        <?php foreach ($semesters as $semester): ?>
                        <option value="<?= e($semester->id) ?>" <?= $activeSemester && $activeSemester->id == $semester->id ? 'selected' : '' ?>>
                            <?= e($semester->name) ?><?= $semester->is_active ? ' (Active)' : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="event_id" class="block text-sm font-medium text-gray-700 mb-2">Event</label>
                    <select id="event_id" name="event_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Events</option>
                        <?php foreach ($events as $event): ?>
                        <option value="<?= e($event->id) ?>"><?= e($event->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <select id="department_id" name="department_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?= e($dept->id) ?>"><?= e($dept->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <?php if ($activeSemester): ?>
            <div class="p-3 bg-blue-50 border-l-4 border-blue-500 rounded-lg">
                <p class="text-sm text-blue-800">
                    <span class="font-semibold">Active Semester:</span> <?= e($activeSemester->name) ?>
                    <span class="text-blue-600">(<?= e($activeSemester->start_date->format('M d, Y')) ?> - <?= e($activeSemester->end_date->format('M d, Y')) ?>)</span>
                </p>
            </div>
            <?php endif; ?>

            <div class="flex space-x-4">
                <button type="button" onclick="exportReport('pdf')" class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Export PDF
                </button>
                <button type="button" onclick="exportReport('excel')" class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Excel
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Events</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?= e(Event::count()) ?></p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Attendance</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?= e(Attendance::count()) ?></p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">This Week</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?= e(Attendance::where('timestamp', '>=', now()->startOfWeek()->toDateTimeString())->count()) ?></p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">This Month</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?= e(Attendance::whereMonth('timestamp', now()->month)->count()) ?></p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Report Templates</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if ($activeSemester): ?>
            <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-500 cursor-pointer transition-colors">
                <h3 class="font-semibold text-gray-900 mb-2">Active Semester Report</h3>
                <p class="text-sm text-gray-600 mb-4">Complete report for <?= e($activeSemester->name) ?></p>
                <button onclick="exportReport('pdf', {semester_id: '<?= e($activeSemester->id) ?>'})" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Generate &rarr;</button>
            </div>
            <?php endif; ?>

            <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-500 cursor-pointer transition-colors">
                <h3 class="font-semibold text-gray-900 mb-2">All Events Report</h3>
                <p class="text-sm text-gray-600 mb-4">Complete attendance report for all events</p>
                <button onclick="exportReport('pdf', {})" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Generate &rarr;</button>
            </div>

            <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-500 cursor-pointer transition-colors">
                <h3 class="font-semibold text-gray-900 mb-2">This Month Report</h3>
                <p class="text-sm text-gray-600 mb-4">Attendance report for current month</p>
                <button onclick="exportReport('pdf', {start_date: '<?= e(now()->startOfMonth()->format('Y-m-d')) ?>', end_date: '<?= e(now()->format('Y-m-d')) ?>'})" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Generate &rarr;</button>
            </div>

            <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-500 cursor-pointer transition-colors">
                <h3 class="font-semibold text-gray-900 mb-2">Department Summary</h3>
                <p class="text-sm text-gray-600 mb-4">Attendance summary by department</p>
                <button onclick="alert('Select a department from filters above')" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Generate &rarr;</button>
            </div>

            <?php foreach ($semesters->take(2) as $semester): ?>
            <?php if (!$semester->is_active): ?>
            <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-500 cursor-pointer transition-colors">
                <h3 class="font-semibold text-gray-900 mb-2"><?= e($semester->name) ?> Report</h3>
                <p class="text-sm text-gray-600 mb-4"><?= e($semester->start_date->format('M d, Y')) ?> - <?= e($semester->end_date->format('M d, Y')) ?></p>
                <button onclick="exportReport('pdf', {semester_id: '<?= e($semester->id) ?>'})" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Generate &rarr;</button>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endsection(); ?>

<?php push('scripts'); ?>
<script>
function exportReport(format, customParams = {}) {
    const form = document.getElementById('reportForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    for (let [key, value] of formData.entries()) { if (value) params.append(key, value); }
    for (let [key, value] of Object.entries(customParams)) { params.append(key, value); }
    const url = format === 'pdf' ? '<?= route('reports.pdf') ?>' : '<?= route('reports.excel') ?>';
    window.open(url + '?' + params.toString(), '_blank');
}
</script>
<?php endpush(); ?>
