<?php layout('layouts.app'); ?>
<?php section('title', 'Add Student - SAMS'); ?>

<?php section('content'); ?>
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Add New Student</h1>
        <a href="<?= route('students.index') ?>" class="text-gray-600 hover:text-gray-900">&larr; Back to Students</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="<?= route('students.store') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="space-y-6">
                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Student ID *</label>
                    <input type="text" id="student_id" name="student_id" value="<?= e(old('student_id')) ?>" placeholder="e.g., VVU2024001" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('student_id') ? 'border-red-500' : '' ?>">
                    <?php if ($errors->has('student_id')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('student_id')) ?></p><?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                        <input type="text" id="first_name" name="first_name" value="<?= e(old('first_name')) ?>" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('first_name') ? 'border-red-500' : '' ?>">
                        <?php if ($errors->has('first_name')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('first_name')) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" value="<?= e(old('last_name')) ?>" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('last_name') ? 'border-red-500' : '' ?>">
                        <?php if ($errors->has('last_name')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('last_name')) ?></p><?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Photo</label>
                    <input type="file" id="photo" name="photo" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('photo') ? 'border-red-500' : '' ?>">
                    <p class="mt-1 text-xs text-gray-500">Maximum file size: 2MB. Accepted formats: JPG, PNG, GIF</p>
                    <?php if ($errors->has('photo')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('photo')) ?></p><?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <select id="department_id" name="department_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('department_id') ? 'border-red-500' : '' ?>">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?= e($dept->id) ?>" <?= old('department_id') == $dept->id ? 'selected' : '' ?>><?= e($dept->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($errors->has('department_id')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('department_id')) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label for="program_id" class="block text-sm font-medium text-gray-700 mb-2">Program</label>
                        <select id="program_id" name="program_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('program_id') ? 'border-red-500' : '' ?>">
                            <option value="">Select Program</option>
                            <?php foreach ($departments as $dept): ?>
                                <?php foreach ($dept->programs as $program): ?>
                                <option value="<?= e($program->id) ?>" <?= old('program_id') == $program->id ? 'selected' : '' ?>><?= e($program->name) ?></option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($errors->has('program_id')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('program_id')) ?></p><?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700 mb-2">Level *</label>
                    <select id="level" name="level" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('level') ? 'border-red-500' : '' ?>">
                        <option value="">Select Level</option>
                        <?php foreach ([100, 200, 300, 400] as $lvl): ?>
                        <option value="<?= $lvl ?>" <?= old('level') == (string) $lvl ? 'selected' : '' ?>><?= $lvl ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($errors->has('level')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('level')) ?></p><?php endif; ?>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" value="1" <?= old('is_active', true) ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Active Student</label>
                </div>
            </div>

            <div class="flex space-x-4 mt-8">
                <button type="submit" class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Add Student</button>
                <a href="<?= route('students.index') ?>" class="flex-1 text-center bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php endsection(); ?>
