<?php $appLogo = brand_logo(); ?>
<?php $appName = \App\Models\SystemSetting::get('app_name', 'SAMS'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= section_yield('title', 'SAMS - Student Attendance Management System') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81' },
                        accent: { 50:'#fdf4ff',100:'#fae8ff',200:'#f5d0fe',300:'#f0abfc',400:'#e879f9',500:'#d946ef',600:'#c026d3',700:'#a21caf',800:'#86198f',900:'#701a75' },
                    },
                    fontFamily: { sans: ['Inter','system-ui','sans-serif'] },
                    boxShadow: {
                        'soft':'0 2px 15px 0 rgba(0,0,0,0.05)',
                        'medium':'0 4px 25px 0 rgba(0,0,0,0.1)',
                        'strong':'0 10px 40px 0 rgba(0,0,0,0.15)',
                        'glow':'0 0 20px rgba(99,102,241,0.3)',
                        'glow-lg':'0 0 30px rgba(99,102,241,0.4)',
                    },
                    animation: {
                        'fade-in':'fadeIn 0.5s ease-in-out',
                        'slide-in':'slideIn 0.3s ease-out',
                        'slide-up':'slideUp 0.4s ease-out',
                        'scale-in':'scaleIn 0.3s ease-out',
                        'bounce-soft':'bounceSoft 2s infinite',
                    },
                    keyframes: {
                        fadeIn: { '0%':{opacity:'0'}, '100%':{opacity:'1'} },
                        slideIn: { '0%':{transform:'translateX(-100%)'}, '100%':{transform:'translateX(0)'} },
                        slideUp: { '0%':{transform:'translateY(20px)',opacity:'0'}, '100%':{transform:'translateY(0)',opacity:'1'} },
                        scaleIn: { '0%':{transform:'scale(0.9)',opacity:'0'}, '100%':{transform:'scale(1)',opacity:'1'} },
                        bounceSoft: { '0%,100%':{transform:'translateY(0)'}, '50%':{transform:'translateY(-5px)'} },
                    },
                },
            },
        }
    </script>

    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="antialiased" x-data="{ sidebarOpen: false, sidebarCollapsed: false }" x-cloak>
    <div class="min-h-screen flex bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 transform transition-all duration-300 ease-in-out shadow-strong flex flex-col"
               :class="[
                   sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                   sidebarCollapsed ? 'w-16' : 'w-64'
               ]"
               style="background: linear-gradient(180deg, #1e3c72 0%, #2a5298 50%, #1e3c72 100%);">

            <div class="flex-shrink-0 flex items-center justify-between h-16 px-4 border-b border-white/10 backdrop-blur-sm">
                <div class="flex items-center space-x-2" :class="sidebarCollapsed && 'justify-center w-full'">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center backdrop-blur-sm shadow-glow overflow-hidden <?= $appLogo ? 'bg-white border border-white p-0.5' : 'bg-gradient-to-br from-white/20 to-white/5 border border-white/20' ?>">
                        <?php if ($appLogo): ?>
                        <img src="<?= e($appLogo) ?>" alt="Logo" class="w-full h-full object-contain">
                        <?php else: ?>
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <?php endif; ?>
                    </div>
                    <div x-show="!sidebarCollapsed" class="transition-all duration-300">
                        <h1 class="text-lg font-bold text-white tracking-wide"><?= e($appName) ?></h1>
                        <p class="text-xs text-white/70">Attendance System</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-white/80 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <nav class="flex-1 mt-4 px-2 pb-4 space-y-1 overflow-y-auto custom-scrollbar">
                <a href="<?= route('dashboard') ?>"
                   class="nav-item flex items-center px-3 py-2.5 rounded-lg text-white/90 hover:text-white hover:bg-white/10 transition-all duration-200 group <?= request()->routeIs('dashboard') ? 'bg-white/15 text-white shadow-medium' : '' ?>"
                   :class="sidebarCollapsed && 'justify-center'">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-blue-400/20 to-blue-600/20 group-hover:from-blue-400/30 group-hover:to-blue-600/30 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <span x-show="!sidebarCollapsed" class="ml-2.5 text-sm font-medium">Dashboard</span>
                </a>

                <a href="<?= route('attendance.index') ?>"
                   class="nav-item flex items-center px-3 py-2.5 rounded-lg text-white/90 hover:text-white hover:bg-white/10 transition-all duration-200 group <?= request()->routeIs('attendance.*') ? 'bg-white/15 text-white shadow-medium' : '' ?>"
                   :class="sidebarCollapsed && 'justify-center'">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-green-400/20 to-green-600/20 group-hover:from-green-400/30 group-hover:to-green-600/30 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <span x-show="!sidebarCollapsed" class="ml-2.5 text-sm font-medium">Attendance</span>
                </a>

                <a href="<?= route('students.index') ?>"
                   class="nav-item flex items-center px-3 py-2.5 rounded-lg text-white/90 hover:text-white hover:bg-white/10 transition-all duration-200 group <?= request()->routeIs('students.*') ? 'bg-white/15 text-white shadow-medium' : '' ?>"
                   :class="sidebarCollapsed && 'justify-center'">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-purple-400/20 to-purple-600/20 group-hover:from-purple-400/30 group-hover:to-purple-600/30 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <span x-show="!sidebarCollapsed" class="ml-2.5 text-sm font-medium">Students</span>
                </a>

                <a href="<?= route('events.index') ?>"
                   class="nav-item flex items-center px-3 py-2.5 rounded-lg text-white/90 hover:text-white hover:bg-white/10 transition-all duration-200 group <?= request()->routeIs('events.*') ? 'bg-white/15 text-white shadow-medium' : '' ?>"
                   :class="sidebarCollapsed && 'justify-center'">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-orange-400/20 to-orange-600/20 group-hover:from-orange-400/30 group-hover:to-orange-600/30 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span x-show="!sidebarCollapsed" class="ml-2.5 text-sm font-medium">Events</span>
                </a>

                <a href="<?= route('reports.index') ?>"
                   class="nav-item flex items-center px-3 py-2.5 rounded-lg text-white/90 hover:text-white hover:bg-white/10 transition-all duration-200 group <?= request()->routeIs('reports.*') ? 'bg-white/15 text-white shadow-medium' : '' ?>"
                   :class="sidebarCollapsed && 'justify-center'">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-pink-400/20 to-pink-600/20 group-hover:from-pink-400/30 group-hover:to-pink-600/30 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span x-show="!sidebarCollapsed" class="ml-2.5 text-sm font-medium">Reports</span>
                </a>

                <?php if (auth()->user()->isAdmin()): ?>
                <div x-show="!sidebarCollapsed" class="pt-3 pb-1.5">
                    <p class="px-3 text-xs font-semibold text-white/50 uppercase tracking-wider">Administration</p>
                </div>

                <a href="<?= route('users.index') ?>"
                   class="nav-item flex items-center px-3 py-2.5 rounded-lg text-white/90 hover:text-white hover:bg-white/10 transition-all duration-200 group <?= request()->routeIs('users.*') ? 'bg-white/15 text-white shadow-medium' : '' ?>"
                   :class="sidebarCollapsed && 'justify-center'">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-400/20 to-cyan-600/20 group-hover:from-cyan-400/30 group-hover:to-cyan-600/30 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <span x-show="!sidebarCollapsed" class="ml-2.5 text-sm font-medium">Users</span>
                </a>

                <a href="<?= route('audit.index') ?>"
                   class="nav-item flex items-center px-3 py-2.5 rounded-lg text-white/90 hover:text-white hover:bg-white/10 transition-all duration-200 group <?= request()->routeIs('audit.*') ? 'bg-white/15 text-white shadow-medium' : '' ?>"
                   :class="sidebarCollapsed && 'justify-center'">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-yellow-400/20 to-yellow-600/20 group-hover:from-yellow-400/30 group-hover:to-yellow-600/30 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span x-show="!sidebarCollapsed" class="ml-2.5 text-sm font-medium">Audit Logs</span>
                </a>

                <a href="<?= route('settings.index') ?>"
                   class="nav-item flex items-center px-3 py-2.5 rounded-lg text-white/90 hover:text-white hover:bg-white/10 transition-all duration-200 group <?= request()->routeIs('settings.*') ? 'bg-white/15 text-white shadow-medium' : '' ?>"
                   :class="sidebarCollapsed && 'justify-center'">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-gray-400/20 to-gray-600/20 group-hover:from-gray-400/30 group-hover:to-gray-600/30 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span x-show="!sidebarCollapsed" class="ml-2.5 text-sm font-medium">Settings</span>
                </a>
                <?php endif; ?>
            </nav>

            <div class="flex-shrink-0 p-3 border-t border-white/10 backdrop-blur-sm hidden lg:block">
                <button @click="sidebarCollapsed = !sidebarCollapsed"
                        class="w-full flex items-center justify-center px-3 py-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-all duration-200"
                        :class="sidebarCollapsed && 'justify-center'">
                    <svg class="w-4 h-4 transition-transform duration-300" :class="sidebarCollapsed && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-2 text-xs font-medium">Collapse</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen overflow-x-hidden max-w-full transition-all duration-300"
             :class="sidebarCollapsed ? 'lg:ml-16' : 'lg:ml-64'">
            <header class="fixed top-0 right-0 bg-white/80 backdrop-blur-md shadow-soft z-40 border-b border-gray-200/50 transition-all duration-300"
                    :style="'left: ' + (window.innerWidth >= 1024 ? (sidebarCollapsed ? '4rem' : '16rem') : '0')">
                <div class="flex items-center justify-between h-20 lg:h-16 px-6 lg:px-8">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        <div class="hidden md:block">
                            <h2 class="text-xl font-semibold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                <?= section_yield('page-title', 'Dashboard') ?>
                            </h2>
                            <p class="text-sm text-gray-500"><?= section_yield('page-subtitle', 'Welcome back!') ?></p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <button class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors group">
                            <svg class="w-6 h-6 text-gray-600 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                        </button>

                        <?php $__avatar = user_avatar(auth()->user()); ?>
                        <div class="flex items-center space-x-3 pl-4 border-l border-gray-200">
                            <a href="<?= route('profile.edit') ?>" class="hidden sm:block text-right group" title="Edit profile">
                                <p class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors"><?= e(auth()->user()->name) ?></p>
                                <p class="text-xs text-gray-500"><?= e(ucfirst(auth()->user()->role)) ?></p>
                            </a>
                            <a href="<?= route('profile.edit') ?>" class="relative" title="Edit profile">
                                <?php if ($__avatar): ?>
                                <img src="<?= e($__avatar) ?>" alt="Profile" class="w-10 h-10 rounded-xl object-cover shadow-medium hover:shadow-glow transition-all">
                                <?php else: ?>
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold shadow-medium hover:shadow-glow transition-all cursor-pointer">
                                    <?= e(strtoupper(substr(auth()->user()->name, 0, 1))) ?>
                                </div>
                                <?php endif; ?>
                            </a>
                            <form action="<?= route('logout') ?>" method="POST" class="inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="p-2 rounded-lg text-gray-600 hover:text-red-600 hover:bg-red-50 transition-all" title="Logout">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-3 sm:px-6 lg:px-8 pb-6 lg:pb-8 overflow-x-hidden max-w-full" style="padding-top: 6rem;">
                <style>
                    @media (min-width: 1024px) { main { padding-top: 5rem !important; } }
                </style>

                <?php if (session('success')): ?>
                <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-lg shadow-soft animate-slide-up">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800"><?= e(session('success')) ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (session('error')): ?>
                <div class="mb-6 p-4 bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-500 rounded-lg shadow-soft animate-slide-up">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800"><?= e(session('error')) ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?= section_yield('content') ?>
            </main>
        </div>
    </div>

    <div x-show="sidebarOpen"
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm lg:hidden"></div>

    <script src="<?= asset('js/app.js') ?>"></script>
    <?= stack('scripts') ?>
</body>
</html>
