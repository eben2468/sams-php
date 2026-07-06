<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title>Login - SAMS</title>
    <?php $__favLogo = brand_logo(); ?>
    <?php if ($__favLogo): ?>
    <link rel="icon" type="image/svg+xml" href="<?= e(route('branding.favicon')) ?>">
    <link rel="icon" href="<?= e($__favLogo) ?>">
    <link rel="apple-touch-icon" href="<?= e($__favLogo) ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        .password-toggle { cursor: pointer; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <?php $appLogo = brand_logo(); ?>
        <?php $appName = \App\Models\SystemSetting::get('app_name', 'SAMS'); ?>
        <div class="text-center mb-8">
            <?php if ($appLogo): ?>
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-xl mb-4 shadow-sm overflow-hidden">
                <img src="<?= e($appLogo) ?>" alt="Logo" class="w-full h-full object-contain">
            </div>
            <?php else: ?>
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-700 rounded-xl mb-4">
                <span class="text-white text-2xl font-bold"><?= e(strtoupper(substr($appName, 0, 1))) ?></span>
            </div>
            <?php endif; ?>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Welcome to <?= e($appName) ?></h1>
            <p class="text-gray-500 text-sm">Student Attendance Management System</p>
            <p class="text-gray-500 text-sm">Valley View University</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-8">
            <h2 class="text-xl font-semibold text-gray-900 text-center mb-6">Sign In</h2>

            <?php if ($errors->any()): ?>
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors->all() as $error): ?>
                    <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form action="<?= route('login') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-900 mb-2">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input type="email" id="email" name="email"
                               value="<?= e(old('email')) ?>"
                               placeholder="you@vvu.edu.gh" required autofocus
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-900 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input type="password" id="password" name="password"
                               placeholder="Enter your password" required
                               class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="h-5 w-5 text-gray-400 password-toggle" fill="none" stroke="currentColor" viewBox="0 0 24 24" onclick="togglePassword()">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-blue-700 text-white py-2.5 px-4 rounded-lg hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors font-medium text-sm">
                    Sign In
                </button>
            </form>
        </div>

        <p class="text-center text-gray-500 text-xs mt-6">
            &copy; <?= date('Y') ?> Valley View University. All rights reserved.
        </p>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        }
    </script>
</body>
</html>
