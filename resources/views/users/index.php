<?php layout('layouts.app'); ?>
<?php section('title', 'Users - SAMS'); ?>

<?php section('content'); ?>
<div class="space-y-6" x-data="userManagement()">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
        <button @click="showCreateModal = true" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Add User</button>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" action="<?= route('users.index') ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <select id="role" name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Roles</option>
                    <option value="admin" <?= request('role') == 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="officer" <?= request('role') == 'officer' ? 'selected' : '' ?>>Officer</option>
                    <option value="supervisor" <?= request('role') == 'supervisor' ? 'selected' : '' ?>>Supervisor</option>
                </select>
            </div>

            <div>
                <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="is_active" name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Status</option>
                    <option value="1" <?= request('is_active') == '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= request('is_active') == '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty = true; foreach ($users as $user): $__empty = false; ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= e($user->name) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= e($user->email) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : ($user->role === 'supervisor' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') ?>"><?= e(ucfirst($user->role)) ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>"><?= $user->is_active ? 'Active' : 'Inactive' ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= e($user->created_at ? $user->created_at->format('M d, Y') : '-') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button @click="editUser(<?= e($user->id) ?>)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                            <?php if ((int) $user->id !== (int) auth()->id()): ?>
                            <form action="<?= route('users.destroy', $user->id) ?>" method="POST" class="inline">
                                <?= csrf_field() ?>
                                <?= method_field('DELETE') ?>
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this user?')" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; if ($__empty): ?>
                    <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200"><?= $users->links() ?></div>
    </div>

    <div x-show="showCreateModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add New User</h3>
                <button @click="showCreateModal = false" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="<?= route('users.store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input type="text" id="name" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" id="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <select id="role" name="role" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="officer">Officer</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active_new" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="is_active_new" class="ml-2 text-sm text-gray-700">Active</label>
                    </div>
                </div>

                <div class="flex space-x-2 mt-6">
                    <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Create User</button>
                    <button type="button" @click="showCreateModal = false" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endsection(); ?>

<?php push('scripts'); ?>
<script>
function userManagement() {
    return {
        showCreateModal: false,
        editUser(userId) {
            alert('Edit functionality coming soon! User ID: ' + userId);
        }
    }
}
</script>
<?php endpush(); ?>
