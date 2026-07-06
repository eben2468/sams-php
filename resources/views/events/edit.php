<?php layout('layouts.app'); ?>
<?php section('title', 'Edit Event - SAMS'); ?>

<?php section('content'); ?>
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Edit Event</h1>
        <a href="<?= route('events.index') ?>" class="text-gray-600 hover:text-gray-900">&larr; Back to Events</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="<?= route('events.update', $event->id) ?>" method="POST">
            <?= csrf_field() ?>
            <?= method_field('PUT') ?>

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Event Name *</label>
                    <input type="text" id="name" name="name" value="<?= e(old('name', $event->name)) ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('name') ? 'border-red-500' : '' ?>">
                    <?php if ($errors->has('name')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('name')) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Event Type *</label>
                    <?php $type = old('type', $event->type); ?>
                    <select id="type" name="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('type') ? 'border-red-500' : '' ?>">
                        <option value="">Select Event Type</option>
                        <option value="church_service" <?= $type == 'church_service' ? 'selected' : '' ?>>Church Service</option>
                        <option value="special_program" <?= $type == 'special_program' ? 'selected' : '' ?>>Special Program</option>
                        <option value="week_of_emphasis" <?= $type == 'week_of_emphasis' ? 'selected' : '' ?>>Week of Emphasis</option>
                        <option value="idf" <?= $type == 'idf' ? 'selected' : '' ?>>Inter-Disciplinary Forum (IDF)</option>
                    </select>
                    <?php if ($errors->has('type')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('type')) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="semester_id" class="block text-sm font-medium text-gray-700 mb-2">Semester</label>
                    <select id="semester_id" name="semester_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('semester_id') ? 'border-red-500' : '' ?>">
                        <option value="">Select Semester</option>
                        <?php foreach ($semesters as $semester): ?>
                        <option value="<?= e($semester->id) ?>" <?= old('semester_id', $event->semester_id) == $semester->id ? 'selected' : '' ?>>
                            <?= e($semester->name) ?><?= $semester->is_active ? ' (Active)' : '' ?> - <?= e($semester->start_date->format('M d, Y')) ?> to <?= e($semester->end_date->format('M d, Y')) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($errors->has('semester_id')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('semester_id')) ?></p><?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">Start Date & Time *</label>
                        <input type="datetime-local" id="start_time" name="start_time" value="<?= e(old('start_time', $event->start_time->format('Y-m-d\TH:i'))) ?>" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('start_time') ? 'border-red-500' : '' ?>">
                        <?php if ($errors->has('start_time')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('start_time')) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">End Date & Time *</label>
                        <input type="datetime-local" id="end_time" name="end_time" value="<?= e(old('end_time', $event->end_time->format('Y-m-d\TH:i'))) ?>" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('end_time') ? 'border-red-500' : '' ?>">
                        <?php if ($errors->has('end_time')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('end_time')) ?></p><?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent <?= $errors->has('description') ? 'border-red-500' : '' ?>"><?= e(old('description', $event->description)) ?></textarea>
                    <?php if ($errors->has('description')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('description')) ?></p><?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assign Officers</label>
                    <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-4">
                        <?php $assigned = old('officer_ids', $event->officers->pluck('id')->toArray()); ?>
                        <?php foreach ($officers as $officer): ?>
                        <label class="flex items-center">
                            <input type="checkbox" name="officer_ids[]" value="<?= e($officer->id) ?>" <?= in_array($officer->id, $assigned) ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700"><?= e($officer->name) ?> (<?= e($officer->email) ?>)</span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($errors->has('officer_ids')): ?><p class="mt-1 text-sm text-red-600"><?= e($errors->first('officer_ids')) ?></p><?php endif; ?>
                </div>
            </div>

            <div class="flex space-x-4 mt-8">
                <button type="submit" class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Update Event</button>
                <a href="<?= route('events.index') ?>" class="flex-1 text-center bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php endsection(); ?>
