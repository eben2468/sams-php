<?php layout('layouts.app'); ?>
<?php section('title', 'Dashboard - SAMS'); ?>
<?php section('page-title', 'Dashboard'); ?>
<?php section('page-subtitle', 'Welcome back, ' . auth()->user()->name . '!'); ?>

<?php section('content'); ?>
<div class="space-y-8 animate-fade-in">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="group relative bg-white rounded-xl shadow-soft hover:shadow-strong transition-all duration-300 overflow-hidden card-hover">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-blue-400/20 to-blue-600/20 rounded-full -mr-12 -mt-12 group-hover:scale-110 transition-transform duration-300"></div>
            <div class="relative p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 shadow-medium group-hover:shadow-glow transition-all">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full">Active</span>
                </div>
                <h3 class="text-xs font-medium text-gray-600 mb-1">Total Students</h3>
                <p class="text-2xl font-bold text-gray-900 mb-1"><?= number_format($stats['total_students']) ?></p>
                <div class="flex items-center text-xs text-green-600">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span class="font-medium">Enrolled</span>
                </div>
            </div>
        </div>

        <div class="group relative bg-white rounded-xl shadow-soft hover:shadow-strong transition-all duration-300 overflow-hidden card-hover">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-green-400/20 to-green-600/20 rounded-full -mr-12 -mt-12 group-hover:scale-110 transition-transform duration-300"></div>
            <div class="relative p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-green-500 to-green-600 shadow-medium group-hover:shadow-glow transition-all">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-semibold text-green-700 bg-green-100 rounded-full">All Time</span>
                </div>
                <h3 class="text-xs font-medium text-gray-600 mb-1">Total Events</h3>
                <p class="text-2xl font-bold text-gray-900 mb-1"><?= number_format($stats['total_events']) ?></p>
                <div class="flex items-center text-xs text-gray-500">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium">Scheduled</span>
                </div>
            </div>
        </div>

        <div class="group relative bg-white rounded-xl shadow-soft hover:shadow-strong transition-all duration-300 overflow-hidden card-hover">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-purple-400/20 to-purple-600/20 rounded-full -mr-12 -mt-12 group-hover:scale-110 transition-transform duration-300"></div>
            <div class="relative p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 shadow-medium group-hover:shadow-glow transition-all">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-semibold text-purple-700 bg-purple-100 rounded-full">Records</span>
                </div>
                <h3 class="text-xs font-medium text-gray-600 mb-1">Total Attendance</h3>
                <p class="text-2xl font-bold text-gray-900 mb-1"><?= number_format($stats['total_attendance']) ?></p>
                <div class="flex items-center text-xs text-purple-600">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium">Verified</span>
                </div>
            </div>
        </div>

        <div class="group relative bg-white rounded-xl shadow-soft hover:shadow-strong transition-all duration-300 overflow-hidden card-hover">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-orange-400/20 to-orange-600/20 rounded-full -mr-12 -mt-12 group-hover:scale-110 transition-transform duration-300"></div>
            <div class="relative p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-orange-500 to-orange-600 shadow-medium group-hover:shadow-glow transition-all">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-semibold text-orange-700 bg-orange-100 rounded-full animate-pulse">Live</span>
                </div>
                <h3 class="text-xs font-medium text-gray-600 mb-1">Active Events</h3>
                <p class="text-2xl font-bold text-gray-900 mb-1"><?= number_format($stats['active_events']) ?></p>
                <div class="flex items-center text-xs text-orange-600">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span class="font-medium">Ongoing</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-soft hover:shadow-medium transition-all duration-300 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Attendance Trend</h2>
                        <p class="text-sm text-gray-600 mt-1">Last 7 days performance</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-medium">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="attendanceTrendChart" class="w-full" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">Department Summary</h2>
            <div class="relative">
                <canvas id="departmentChart" class="w-full" style="max-height: 400px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="bg-white rounded-2xl shadow-soft hover:shadow-medium transition-all duration-300 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Recent Activities</h2>
                    <p class="text-sm text-gray-600 mt-1">Latest attendance records</p>
                </div>
                <a href="<?= route('attendance.index') ?>" class="px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-all">View All</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Officer</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php $__empty = true; foreach ($stats['recent_activities'] as $activity): $__empty = false; ?>
                    <tr class="table-row-hover">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold shadow-soft">
                                    <?= e(substr((string) $activity['student_name'], 0, 1)) ?>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900"><?= e($activity['student_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= e($activity['student_id']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= e($activity['event_name']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-700"><?= e($activity['officer_name']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1.5 text-xs font-bold rounded-lg shadow-soft <?= $activity['method'] === 'scan' ? 'bg-gradient-to-r from-green-400 to-green-500 text-white' : ($activity['method'] === 'manual' ? 'bg-gradient-to-r from-yellow-400 to-yellow-500 text-white' : 'bg-gradient-to-r from-blue-400 to-blue-500 text-white') ?>">
                                <?= e(ucfirst($activity['method'])) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-600"><?= e($activity['timestamp']->diffForHumans()) ?></div>
                        </td>
                    </tr>
                    <?php endforeach; if ($__empty): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            <p class="mt-4 text-sm text-gray-500">No recent activities found.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endsection(); ?>

<?php push('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = '#6B7280';

    const trendCtx = document.getElementById('attendanceTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($stats['attendance_trend'], 'date')) ?>,
            datasets: [{
                label: 'Attendance',
                data: <?= json_encode(array_column($stats['attendance_trend'], 'count')) ?>,
                borderColor: '#667eea', backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3, tension: 0.4, fill: true,
                pointBackgroundColor: '#667eea', pointBorderColor: '#fff', pointBorderWidth: 2,
                pointRadius: 5, pointHoverRadius: 7, pointHoverBackgroundColor: '#764ba2',
                pointHoverBorderColor: '#fff', pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, titleFont: { size: 14, weight: 'bold' }, bodyFont: { size: 13 } } },
            scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { padding: 10 } }, x: { grid: { display: false }, ticks: { padding: 10 } } }
        }
    });

    const deptCtx = document.getElementById('departmentChart').getContext('2d');
    const departmentLabels = <?= json_encode($stats['attendance_by_department']->pluck('name')) ?>;
    const departmentData = <?= json_encode($stats['attendance_by_department']->pluck('count')) ?>;
    const barColors = departmentData.map((_, index) => index === departmentData.length - 1 ? '#06b6d4' : '#1e3a8a');

    new Chart(deptCtx, {
        type: 'bar',
        data: { labels: departmentLabels, datasets: [{ label: 'Programs', data: departmentData, backgroundColor: barColors, borderRadius: 6, borderSkipped: false, barPercentage: 0.6, categoryPercentage: 0.8 }] },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, titleFont: { size: 14, weight: 'bold' }, bodyFont: { size: 13 }, callbacks: { label: (c) => 'Programs: ' + c.parsed.y } } },
            scales: {
                y: { beginAtZero: true, max: (departmentData.length ? Math.max(...departmentData) + 1 : 1), grid: { color: 'rgba(0,0,0,0.06)' }, ticks: { padding: 10, stepSize: 1, font: { size: 11 }, color: '#9ca3af' }, border: { display: false } },
                x: { grid: { display: false }, ticks: { padding: 10, font: { size: 11 }, color: '#9ca3af', maxRotation: 45, minRotation: 45 }, border: { display: false } }
            }
        }
    });
</script>
<?php endpush(); ?>
