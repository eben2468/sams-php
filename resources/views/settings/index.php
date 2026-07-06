<?php use Carbon\Carbon; ?>
<?php layout('layouts.app'); ?>
<?php section('title', 'System Settings - SAMS'); ?>
<?php section('page-title', 'System Settings'); ?>
<?php section('page-subtitle', 'Manage academic configurations, semesters, departments, and programs.'); ?>

<?php section('content'); ?>
<div class="space-y-8 animate-fade-in">

    <?php if ($activeSemester): ?>
    <div class="relative overflow-hidden rounded-xl shadow-lg bg-gradient-to-r from-emerald-500 to-green-600">
        <div class="relative p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center justify-center w-14 h-14 rounded-xl bg-white/20 backdrop-blur-sm border-2 border-white/40 shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white uppercase tracking-wider mb-1">Active Semester</p>
                        <h3 class="text-2xl font-bold text-white"><?= e($activeSemester->name) ?></h3>
                        <div class="flex items-center mt-2 space-x-3 text-sm text-white">
                            <div class="flex items-center space-x-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="font-semibold"><?= e(Carbon::parse($activeSemester->start_date)->format('M d, Y')) ?></span>
                            </div>
                            <span class="text-white/80">&rarr;</span>
                            <div class="flex items-center space-x-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="font-semibold"><?= e(Carbon::parse($activeSemester->end_date)->format('M d, Y')) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="px-4 py-2 text-sm font-bold bg-white text-emerald-600 rounded-lg shadow-md">Active</span>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border-l-4 border-amber-500 p-4 rounded-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div class="ml-3">
                <h3 class="text-sm font-semibold text-amber-800">No Active Semester</h3>
                <p class="mt-1 text-sm text-amber-700">Please activate a semester to enable full system functionality.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Branding / Logo -->
    <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200">
        <div class="bg-gradient-to-r from-slate-700 to-gray-800 p-6">
            <div class="flex items-center space-x-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-white/20 backdrop-blur-sm shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Branding</h3>
                    <p class="text-sm text-gray-300 mt-0.5">Set the system name and logo</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <form action="<?= route('settings.logo') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                <?= csrf_field() ?>

                <div>
                    <label for="app_name" class="block text-sm font-semibold text-gray-700 mb-2">System Name *</label>
                    <input type="text" id="app_name" name="app_name" value="<?= e(old('app_name', $settings['app_name'] ?? 'SAMS')) ?>" placeholder="e.g., SAMS" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-transparent <?= $errors->has('app_name') ? 'border-red-500' : '' ?>">
                    <p class="mt-1 text-xs text-gray-500">Shown in the sidebar, login page, and page titles.</p>
                    <?php if ($errors->has('app_name')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('app_name')) ?></p><?php endif; ?>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-end gap-6">
                    <?php $currentLogo = brand_logo(); ?>
                    <div class="flex items-center justify-center w-24 h-24 rounded-xl bg-gray-100 border border-gray-200 overflow-hidden flex-shrink-0">
                        <?php if ($currentLogo): ?>
                        <img src="<?= e($currentLogo) ?>" alt="Current logo" class="w-full h-full object-contain">
                        <?php else: ?>
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <label for="logo" class="block text-sm font-semibold text-gray-700 mb-2">Logo Image</label>
                        <input type="file" id="logo" name="logo" accept="image/*"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-transparent <?= $errors->has('logo') ? 'border-red-500' : '' ?>">
                        <p class="mt-1 text-xs text-gray-500">Optional. Max 2MB. Accepted formats: JPG, PNG, GIF. Leave empty to keep the current logo.</p>
                        <?php if ($errors->has('logo')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('logo')) ?></p><?php endif; ?>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-slate-700 to-gray-800 hover:from-slate-800 hover:to-gray-900 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl">Save Branding</button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- Semesters -->
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-white/20 backdrop-blur-sm shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Semester Management</h3>
                            <p class="text-sm text-blue-100 mt-0.5">Academic period configuration</p>
                        </div>
                    </div>
                    <span class="px-3 py-1.5 text-sm font-bold text-blue-700 bg-white rounded-lg shadow-md"><?= e($semesters->count()) ?></span>
                </div>
            </div>

            <div class="p-6">
                <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                    <?php $__empty = true; foreach ($semesters as $semester): $__empty = false; ?>
                    <div class="group/item relative rounded-xl p-4 transition-all duration-200 <?= $semester->is_active ? 'bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300 shadow-md' : 'bg-gray-50 border border-gray-200 hover:border-blue-300 hover:shadow-md' ?>">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <h4 class="text-base font-bold text-gray-900"><?= e($semester->name) ?></h4>
                                    <?php if ($semester->is_active): ?>
                                    <span class="px-2.5 py-1 text-xs font-bold bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-full shadow-sm">Active</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center space-x-2 text-sm text-gray-600">
                                    <span class="font-medium"><?= e(Carbon::parse($semester->start_date)->format('M d, Y')) ?></span>
                                    <span class="text-gray-400">&rarr;</span>
                                    <span class="font-medium"><?= e(Carbon::parse($semester->end_date)->format('M d, Y')) ?></span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <?php if (!$semester->is_active): ?>
                                <form action="<?= route('semesters.activate', $semester->id) ?>" method="POST" class="inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="px-3 py-1.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold rounded-lg transition-all shadow-md hover:shadow-lg">Activate</button>
                                </form>
                                <?php endif; ?>
                                <button onclick="editSemester(<?= e($semester->id) ?>, '<?= e(addslashes($semester->name)) ?>', '<?= e($semester->start_date->toDateString()) ?>', '<?= e($semester->end_date->toDateString()) ?>')" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <?php if (!$semester->is_active): ?>
                                <form action="<?= route('semesters.destroy', $semester->id) ?>" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this semester?');">
                                    <?= csrf_field() ?>
                                    <?= method_field('DELETE') ?>
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; if ($__empty): ?>
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-sm font-medium text-gray-500">No semesters configured</p>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <button type="button" onclick="openCreateSemesterModal()" class="w-full px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-[1.02]">
                        <span class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Create New Semester</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Departments -->
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-violet-600 to-purple-600 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-white/20 backdrop-blur-sm shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Departments</h3>
                            <p class="text-sm text-purple-100 mt-0.5">Academic departments</p>
                        </div>
                    </div>
                    <span class="px-3 py-1.5 text-sm font-bold text-purple-700 bg-white rounded-lg shadow-md"><?= e($departments->count()) ?></span>
                </div>
            </div>

            <div class="p-6">
                <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                    <?php $__empty = true; foreach ($departments as $department): $__empty = false; ?>
                    <div class="group/item bg-gradient-to-r from-gray-50 to-white border border-gray-200 rounded-xl p-4 hover:border-purple-300 hover:shadow-lg transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3 flex-1 min-w-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-violet-100 to-purple-200 rounded-xl flex items-center justify-center flex-shrink-0 group-hover/item:scale-110 transition-transform shadow-md">
                                    <span class="text-violet-700 font-bold text-base"><?= e(strtoupper(substr($department->name, 0, 2))) ?></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-base font-bold text-gray-900 truncate"><?= e($department->name) ?></h4>
                                    <?php if ($department->code): ?>
                                    <p class="text-sm text-gray-600 mt-0.5">Code: <span class="font-semibold"><?= e($department->code) ?></span></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-3">
                                <button onclick="editDepartment(<?= e($department->id) ?>, '<?= e(addslashes($department->name)) ?>', '<?= e($department->code) ?>')" class="p-2 text-purple-600 hover:bg-purple-100 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form action="<?= route('departments.destroy', $department->id) ?>" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this department?');">
                                    <?= csrf_field() ?>
                                    <?= method_field('DELETE') ?>
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; if ($__empty): ?>
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <p class="text-sm font-medium text-gray-500">No departments configured</p>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <button type="button" onclick="openCreateDepartmentModal()" class="w-full px-5 py-3 bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-700 hover:to-purple-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-[1.02]">
                        <span class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Create New Department</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Programs -->
    <div class="group relative bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100">
        <div class="relative h-32 bg-gradient-to-br from-teal-500 via-emerald-600 to-cyan-700 overflow-hidden">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full -ml-24 -mb-24"></div>
            <div class="relative p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-14 h-14 rounded-xl bg-white/20 backdrop-blur-md border-2 border-white/30 shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white drop-shadow-md">Academic Programs</h3>
                            <p class="text-sm text-white/90 mt-1">Programs and courses offered</p>
                        </div>
                    </div>
                    <span class="px-3 py-1.5 text-sm font-bold text-teal-700 bg-white rounded-full shadow-lg"><?= e($programs->count()) ?></span>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-80 overflow-y-auto pr-2">
                <?php $__empty = true; foreach ($programs as $program): $__empty = false; ?>
                <div class="group/item relative bg-gradient-to-br from-white to-gray-50 border-2 border-gray-200 rounded-xl p-4 hover:border-teal-400 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-start space-x-3 flex-1 min-w-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-teal-400 to-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0 group-hover/item:scale-110 transition-transform shadow-lg">
                                <span class="text-white font-bold text-lg"><?= e(strtoupper(substr($program->name, 0, 1))) ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 leading-tight mb-1"><?= e($program->name) ?></h4>
                                <?php if ($program->code): ?>
                                <p class="text-xs font-semibold text-teal-600 bg-teal-50 px-2 py-0.5 rounded inline-block"><?= e($program->code) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center space-x-1 ml-2">
                            <button onclick="editProgram(<?= e($program->id) ?>, '<?= e(addslashes($program->name)) ?>', '<?= e($program->code) ?>', <?= e($program->department_id) ?>)" class="p-1.5 text-teal-600 hover:bg-teal-100 rounded-lg transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form action="<?= route('programs.destroy', $program->id) ?>" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this program?');">
                                <?= csrf_field() ?>
                                <?= method_field('DELETE') ?>
                                <button type="submit" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php if ($program->department): ?>
                    <div class="flex items-center space-x-2 pt-3 border-t border-gray-200">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span class="text-xs font-medium text-gray-600 truncate"><?= e($program->department->name) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; if ($__empty): ?>
                <div class="col-span-full text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <p class="text-sm font-medium text-gray-500">No programs configured</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200">
                <button type="button" onclick="openCreateProgramModal()" class="w-full px-5 py-3 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-[1.02]">
                    <span class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Create New Program</span>
                    </span>
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Semester Modal -->
<div id="semesterModal" class="modal">
    <div class="modal-content">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4 rounded-t-xl">
            <div class="flex items-center justify-between">
                <h3 id="semesterModalTitle" class="text-lg font-bold text-white">Create New Semester</h3>
                <button type="button" onclick="closeSemesterModal()" class="text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <form id="semesterForm" method="POST" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" id="semesterMethodField" name="_method" value="POST">
            <div class="space-y-4">
                <div>
                    <label for="semesterName" class="block text-sm font-semibold text-gray-700 mb-2">Semester Name *</label>
                    <input type="text" id="semesterName" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Fall 2024">
                </div>
                <div>
                    <label for="semesterStartDate" class="block text-sm font-semibold text-gray-700 mb-2">Start Date *</label>
                    <input type="date" id="semesterStartDate" name="start_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label for="semesterEndDate" class="block text-sm font-semibold text-gray-700 mb-2">End Date *</label>
                    <input type="date" id="semesterEndDate" name="end_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                <button type="button" onclick="closeSemesterModal()" class="px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-sm">Save Semester</button>
            </div>
        </form>
    </div>
</div>

<!-- Department Modal -->
<div id="departmentModal" class="modal">
    <div class="modal-content">
        <div class="bg-gradient-to-r from-violet-500 to-purple-600 px-6 py-4 rounded-t-xl">
            <div class="flex items-center justify-between">
                <h3 id="departmentModalTitle" class="text-lg font-bold text-white">Create New Department</h3>
                <button type="button" onclick="closeDepartmentModal()" class="text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <form id="departmentForm" method="POST" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" id="departmentMethodField" name="_method" value="POST">
            <div class="space-y-4">
                <div>
                    <label for="departmentName" class="block text-sm font-semibold text-gray-700 mb-2">Department Name *</label>
                    <input type="text" id="departmentName" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent" placeholder="e.g., Computer Science">
                </div>
                <div>
                    <label for="departmentCode" class="block text-sm font-semibold text-gray-700 mb-2">Department Code</label>
                    <input type="text" id="departmentCode" name="code" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent" placeholder="e.g., CS">
                </div>
            </div>
            <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                <button type="button" onclick="closeDepartmentModal()" class="px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-600 hover:to-purple-700 text-white font-semibold rounded-lg shadow-sm">Save Department</button>
            </div>
        </form>
    </div>
</div>

<!-- Program Modal -->
<div id="programModal" class="modal">
    <div class="modal-content">
        <div class="bg-gradient-to-r from-teal-500 to-emerald-600 px-6 py-4 rounded-t-xl">
            <div class="flex items-center justify-between">
                <h3 id="programModalTitle" class="text-lg font-bold text-white">Create New Program</h3>
                <button type="button" onclick="closeProgramModal()" class="text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <form id="programForm" method="POST" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" id="programMethodField" name="_method" value="POST">
            <div class="space-y-4">
                <div>
                    <label for="programName" class="block text-sm font-semibold text-gray-700 mb-2">Program Name *</label>
                    <input type="text" id="programName" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent" placeholder="e.g., Bachelor of Science in Computer Science">
                </div>
                <div>
                    <label for="programCode" class="block text-sm font-semibold text-gray-700 mb-2">Program Code</label>
                    <input type="text" id="programCode" name="code" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent" placeholder="e.g., BSCS">
                </div>
                <div>
                    <label for="programDepartment" class="block text-sm font-semibold text-gray-700 mb-2">Department *</label>
                    <select id="programDepartment" name="department_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?= e($dept->id) ?>"><?= e($dept->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                <button type="button" onclick="closeProgramModal()" class="px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-600 hover:to-emerald-700 text-white font-semibold rounded-lg shadow-sm">Save Program</button>
            </div>
        </form>
    </div>
</div>
<?php endsection(); ?>

<?php push('scripts'); ?>
<style>
    .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); }
    .modal.active { display: flex; align-items: center; justify-content: center; animation: fadeIn 0.2s ease-out; }
    .modal-content { background-color: white; border-radius: 0.75rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; animation: slideUp 0.3s ease-out; }
</style>

<script>
    function openCreateSemesterModal() {
        document.getElementById('semesterModalTitle').textContent = 'Create New Semester';
        document.getElementById('semesterForm').reset();
        document.getElementById('semesterForm').action = '<?= route('semesters.store') ?>';
        document.getElementById('semesterMethodField').value = 'POST';
        document.getElementById('semesterModal').classList.add('active');
    }
    function editSemester(id, name, startDate, endDate) {
        document.getElementById('semesterModalTitle').textContent = 'Edit Semester';
        document.getElementById('semesterName').value = name;
        document.getElementById('semesterStartDate').value = startDate;
        document.getElementById('semesterEndDate').value = endDate;
        document.getElementById('semesterForm').action = '<?= url('/semesters') ?>/' + id;
        document.getElementById('semesterMethodField').value = 'PUT';
        document.getElementById('semesterModal').classList.add('active');
    }
    function closeSemesterModal() { document.getElementById('semesterModal').classList.remove('active'); }

    function openCreateDepartmentModal() {
        document.getElementById('departmentModalTitle').textContent = 'Create New Department';
        document.getElementById('departmentForm').reset();
        document.getElementById('departmentForm').action = '<?= route('departments.store') ?>';
        document.getElementById('departmentMethodField').value = 'POST';
        document.getElementById('departmentModal').classList.add('active');
    }
    function editDepartment(id, name, code) {
        document.getElementById('departmentModalTitle').textContent = 'Edit Department';
        document.getElementById('departmentName').value = name;
        document.getElementById('departmentCode').value = code || '';
        document.getElementById('departmentForm').action = '<?= url('/departments') ?>/' + id;
        document.getElementById('departmentMethodField').value = 'PUT';
        document.getElementById('departmentModal').classList.add('active');
    }
    function closeDepartmentModal() { document.getElementById('departmentModal').classList.remove('active'); }

    function openCreateProgramModal() {
        document.getElementById('programModalTitle').textContent = 'Create New Program';
        document.getElementById('programForm').reset();
        document.getElementById('programForm').action = '<?= route('programs.store') ?>';
        document.getElementById('programMethodField').value = 'POST';
        document.getElementById('programModal').classList.add('active');
    }
    function editProgram(id, name, code, departmentId) {
        document.getElementById('programModalTitle').textContent = 'Edit Program';
        document.getElementById('programName').value = name;
        document.getElementById('programCode').value = code || '';
        document.getElementById('programDepartment').value = departmentId;
        document.getElementById('programForm').action = '<?= url('/programs') ?>/' + id;
        document.getElementById('programMethodField').value = 'PUT';
        document.getElementById('programModal').classList.add('active');
    }
    function closeProgramModal() { document.getElementById('programModal').classList.remove('active'); }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) { event.target.classList.remove('active'); }
    }
</script>
<?php endpush(); ?>
