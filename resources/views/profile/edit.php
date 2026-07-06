<?php layout('layouts.app'); ?>
<?php section('title', 'My Profile - SAMS'); ?>
<?php section('page-title', 'My Profile'); ?>
<?php section('page-subtitle', 'Manage your account information'); ?>

<?php section('content'); ?>
<?php $avatarUrl = user_avatar($user); ?>
<div class="max-w-3xl mx-auto space-y-6 animate-fade-in">

    <div class="bg-white rounded-2xl shadow-soft overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 rounded-2xl bg-white/20 border-2 border-white/40 overflow-hidden flex items-center justify-center shadow-lg">
                    <?php if ($avatarUrl): ?>
                    <img src="<?= e($avatarUrl) ?>" alt="Profile" class="w-full h-full object-cover">
                    <?php else: ?>
                    <span class="text-2xl font-bold text-white"><?= e(strtoupper(substr($user->name, 0, 1))) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-white"><?= e($user->name) ?></h2>
                    <p class="text-indigo-100 text-sm"><?= e($user->email) ?> &middot; <?= e(ucfirst($user->role)) ?></p>
                </div>
            </div>
        </div>
    </div>

    <form action="<?= route('profile.update') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <?= csrf_field() ?>
        <?= method_field('PUT') ?>

        <!-- Account details -->
        <div class="bg-white rounded-2xl shadow-soft p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-5">Account Details</h3>

            <!-- Avatar -->
            <div class="flex items-center space-x-5 mb-6">
                <div class="w-20 h-20 rounded-2xl bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center flex-shrink-0">
                    <?php if ($avatarUrl): ?>
                    <img src="<?= e($avatarUrl) ?>" alt="Current avatar" class="w-full h-full object-cover">
                    <?php else: ?>
                    <svg class="w-9 h-9 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <?php endif; ?>
                </div>
                <div class="flex-1">
                    <label for="avatar" class="block text-sm font-semibold text-gray-700 mb-2">Profile Picture</label>
                    <input type="file" id="avatar" name="avatar" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('avatar') ? 'border-red-500' : '' ?>">
                    <p class="mt-1 text-xs text-gray-500">Max 2MB. JPG, PNG, GIF. Leave empty to keep your current picture.</p>
                    <?php if ($errors->has('avatar')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('avatar')) ?></p><?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                    <input type="text" id="name" name="name" value="<?= e(old('name', $user->name)) ?>" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('name') ? 'border-red-500' : '' ?>">
                    <?php if ($errors->has('name')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('name')) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                    <input type="email" id="email" name="email" value="<?= e(old('email', $user->email)) ?>" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('email') ? 'border-red-500' : '' ?>">
                    <?php if ($errors->has('email')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('email')) ?></p><?php endif; ?>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
                <div class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-600"><?= e(ucfirst($user->role)) ?> <span class="text-xs text-gray-400">(managed by an administrator)</span></div>
            </div>
        </div>

        <!-- Change password -->
        <div class="bg-white rounded-2xl shadow-soft p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Change Password</h3>
            <p class="text-sm text-gray-500 mb-5">Leave these blank if you don't want to change your password.</p>

            <div class="space-y-4">
                <div>
                    <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">Current Password</label>
                    <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('current_password') ? 'border-red-500' : '' ?>">
                    <?php if ($errors->has('current_password')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('current_password')) ?></p><?php endif; ?>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                        <input type="password" id="password" name="password" autocomplete="new-password"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('password') ? 'border-red-500' : '' ?>">
                        <p class="mt-1 text-xs text-gray-500">At least 6 characters.</p>
                        <?php if ($errors->has('password')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('password')) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('password_confirmation') ? 'border-red-500' : '' ?>">
                        <?php if ($errors->has('password_confirmation')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('password_confirmation')) ?></p><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex space-x-4">
            <button type="submit" class="flex-1 sm:flex-none sm:px-8 bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 rounded-xl hover:from-indigo-600 hover:to-purple-700 shadow-medium hover:shadow-strong transition-all duration-200 font-semibold">Save Changes</button>
            <a href="<?= route('dashboard') ?>" class="flex-1 sm:flex-none sm:px-8 text-center bg-gray-100 text-gray-700 py-3 rounded-xl hover:bg-gray-200 transition-all duration-200 font-semibold">Cancel</a>
        </div>
    </form>
</div>
<?php endsection(); ?>
