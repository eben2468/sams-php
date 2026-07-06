<?php

use App\Models\AuditLog;

layout('layouts.app');
?>
<?php section('title', 'Audit Logs - SAMS'); ?>

<?php section('content'); ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Audit Logs</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" action="<?= route('audit.index') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="action" class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                <select id="action" name="action" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Actions</option>
                    <?php foreach (['LOGIN' => 'Login', 'LOGOUT' => 'Logout', 'CREATE_STUDENT' => 'Create Student', 'UPDATE_STUDENT' => 'Update Student', 'DELETE_STUDENT' => 'Delete Student', 'CREATE_EVENT' => 'Create Event', 'MARK_ATTENDANCE' => 'Mark Attendance'] as $value => $labelText): ?>
                    <option value="<?= $value ?>" <?= request('action') == $value ? 'selected' : '' ?>><?= $labelText ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="target_type" class="block text-sm font-medium text-gray-700 mb-2">Target Type</label>
                <select id="target_type" name="target_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Types</option>
                    <?php foreach (['user' => 'User', 'student' => 'Student', 'event' => 'Event', 'attendance' => 'Attendance'] as $value => $labelText): ?>
                    <option value="<?= $value ?>" <?= request('target_type') == $value ? 'selected' : '' ?>><?= $labelText ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="<?= e(request('start_date')) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Filter</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty = true; foreach ($logs as $log): $__empty = false; ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= e($log->timestamp->format('M d, Y H:i:s')) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= e($log->performer?->name) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= str_contains($log->action, 'DELETE') ? 'bg-red-100 text-red-800' : (str_contains($log->action, 'CREATE') ? 'bg-green-100 text-green-800' : (str_contains($log->action, 'UPDATE') ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) ?>"><?= e(str_replace('_', ' ', $log->action)) ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= e(ucfirst($log->target_type)) ?>
                            <?php if ($log->target_id): ?>#<?= e($log->target_id) ?><?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php if ($log->metadata): ?>
                                <details class="cursor-pointer">
                                    <summary class="text-indigo-600 hover:text-indigo-800">View Details</summary>
                                    <pre class="mt-2 text-xs bg-gray-50 p-2 rounded"><?= e(json_encode($log->metadata, JSON_PRETTY_PRINT)) ?></pre>
                                </details>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; if ($__empty): ?>
                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No audit logs found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200"><?= $logs->links() ?></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Total Logs</p>
            <p class="text-3xl font-bold text-gray-900 mt-2"><?= e($logs->total()) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Today's Actions</p>
            <p class="text-3xl font-bold text-gray-900 mt-2"><?= e(AuditLog::whereDate('timestamp', today())->count()) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">This Week</p>
            <p class="text-3xl font-bold text-gray-900 mt-2"><?= e(AuditLog::where('timestamp', '>=', now()->startOfWeek()->toDateTimeString())->count()) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">This Month</p>
            <p class="text-3xl font-bold text-gray-900 mt-2"><?= e(AuditLog::whereMonth('timestamp', now()->month)->count()) ?></p>
        </div>
    </div>
</div>
<?php endsection(); ?>
