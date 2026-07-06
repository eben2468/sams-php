<?php layout('layouts.app'); ?>
<?php section('title', 'Edit Student - SAMS'); ?>
<?php section('page-title', 'Edit Student'); ?>
<?php section('page-subtitle', 'Update student information'); ?>

<?php section('content'); ?>
<div class="max-w-3xl mx-auto space-y-6 animate-fade-in">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Student</h1>
            <p class="text-sm text-gray-600 mt-1">Update information for <?= e($student->full_name) ?></p>
        </div>
        <a href="<?= route('students.index') ?>" class="px-4 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Students</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-soft hover:shadow-medium transition-all duration-300 overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-slate-50 to-gray-50 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Student Information</h3>
        </div>

        <form action="<?= route('students.update', $student->id) ?>" method="POST" enctype="multipart/form-data" class="p-6">
            <?= csrf_field() ?>
            <?= method_field('PUT') ?>

            <div class="space-y-6">
                <div>
                    <label for="student_id" class="block text-sm font-semibold text-gray-700 mb-2">Student ID *</label>
                    <input type="text" id="student_id" name="student_id" value="<?= e(old('student_id', $student->student_id)) ?>" placeholder="e.g., VVU2024001" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all <?= $errors->has('student_id') ? 'border-red-500' : '' ?>">
                    <?php if ($errors->has('student_id')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('student_id')) ?></p><?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-2">First Name *</label>
                        <input type="text" id="first_name" name="first_name" value="<?= e(old('first_name', $student->first_name)) ?>" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all <?= $errors->has('first_name') ? 'border-red-500' : '' ?>">
                        <?php if ($errors->has('first_name')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('first_name')) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-semibold text-gray-700 mb-2">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" value="<?= e(old('last_name', $student->last_name)) ?>" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all <?= $errors->has('last_name') ? 'border-red-500' : '' ?>">
                        <?php if ($errors->has('last_name')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('last_name')) ?></p><?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="photo" class="block text-sm font-semibold text-gray-700 mb-2">Photo</label>
                    <?php if ($student->photo): ?>
                    <div class="mb-3 flex items-center space-x-4">
                        <img src="<?= e(asset('uploads/' . $student->photo)) ?>" alt="<?= e($student->full_name) ?>" class="w-20 h-20 rounded-lg object-cover shadow-soft">
                        <div>
                            <p class="text-sm text-gray-600">Current photo</p>
                            <p class="text-xs text-gray-500">Upload a new photo to replace</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <input type="file" id="photo" name="photo" accept="image/*"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all <?= $errors->has('photo') ? 'border-red-500' : '' ?>">
                    <p class="mt-1 text-xs text-gray-500">Maximum file size: 2MB. Accepted formats: JPG, PNG, GIF</p>
                    <?php if ($errors->has('photo')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('photo')) ?></p><?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="department_id" class="block text-sm font-semibold text-gray-700 mb-2">Department</label>
                        <select id="department_id" name="department_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all <?= $errors->has('department_id') ? 'border-red-500' : '' ?>">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?= e($dept->id) ?>" <?= old('department_id', $student->department_id) == $dept->id ? 'selected' : '' ?>><?= e($dept->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($errors->has('department_id')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('department_id')) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label for="program_id" class="block text-sm font-semibold text-gray-700 mb-2">Program</label>
                        <select id="program_id" name="program_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all <?= $errors->has('program_id') ? 'border-red-500' : '' ?>">
                            <option value="">Select Program</option>
                            <?php foreach ($departments as $dept): ?>
                                <?php foreach ($dept->programs as $program): ?>
                                <option value="<?= e($program->id) ?>" <?= old('program_id', $student->program_id) == $program->id ? 'selected' : '' ?>><?= e($program->name) ?></option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($errors->has('program_id')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('program_id')) ?></p><?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="level" class="block text-sm font-semibold text-gray-700 mb-2">Level *</label>
                    <select id="level" name="level" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all <?= $errors->has('level') ? 'border-red-500' : '' ?>">
                        <option value="">Select Level</option>
                        <?php foreach ([100, 200, 300, 400] as $lvl): ?>
                        <option value="<?= $lvl ?>" <?= old('level', $student->level) == (string) $lvl ? 'selected' : '' ?>><?= $lvl ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($errors->has('level')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('level')) ?></p><?php endif; ?>
                </div>

                <div class="flex items-center p-4 bg-gray-50 rounded-xl">
                    <input type="checkbox" id="is_active" name="is_active" value="1" <?= old('is_active', $student->is_active) ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                    <label for="is_active" class="ml-3 text-sm font-medium text-gray-700">Active Student</label>
                </div>
            </div>

            <div class="flex space-x-4 mt-8">
                <button type="submit" class="flex-1 btn-ripple bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-xl hover:from-indigo-600 hover:to-purple-700 shadow-medium hover:shadow-strong transition-all duration-200 font-semibold">Update Student</button>
                <a href="<?= route('students.index') ?>" class="flex-1 text-center bg-gray-100 text-gray-700 px-6 py-3 rounded-xl hover:bg-gray-200 transition-all duration-200 font-semibold">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php endsection(); ?>
