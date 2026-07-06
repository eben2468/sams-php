<?php layout('layouts.app'); ?>
<?php section('title', 'Events - SAMS'); ?>
<?php section('page-title', 'Events Management'); ?>
<?php section('page-subtitle', 'Manage and track all events'); ?>

<?php section('content'); ?>
<div class="space-y-6 animate-fade-in">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Events Calendar</h1>
            <p class="text-sm text-gray-600 mt-1">Total: <?= e($events->total()) ?> events</p>
        </div>
        <?php if (auth()->user()->isAdmin()): ?>
        <a href="<?= route('events.create') ?>" class="btn-ripple px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl hover:from-indigo-600 hover:to-purple-700 shadow-medium hover:shadow-strong transition-all duration-200 font-medium flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <span>Create Event</span>
        </a>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl shadow-soft hover:shadow-medium transition-all duration-300 overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-slate-50 to-gray-50 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter Events
            </h3>
        </div>
        <form method="GET" action="<?= route('events.index') ?>" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="semester_id" class="block text-sm font-semibold text-gray-700 mb-2">Semester</label>
                    <select id="semester_id" name="semester_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <option value="">Active Semester</option>
                        <option value="all" <?= request('semester_id') == 'all' ? 'selected' : '' ?>>All Semesters</option>
                        <?php foreach ($semesters as $semester): ?>
                        <option value="<?= e($semester->id) ?>" <?= request('semester_id') == $semester->id ? 'selected' : '' ?>>
                            <?= e($semester->name) ?><?= $semester->is_active ? ' (Active)' : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">Event Type</label>
                    <select id="type" name="type" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <option value="">All Types</option>
                        <option value="church_service" <?= request('type') == 'church_service' ? 'selected' : '' ?>>Church Service</option>
                        <option value="special_program" <?= request('type') == 'special_program' ? 'selected' : '' ?>>Special Program</option>
                        <option value="week_of_emphasis" <?= request('type') == 'week_of_emphasis' ? 'selected' : '' ?>>Week of Emphasis</option>
                        <option value="idf" <?= request('type') == 'idf' ? 'selected' : '' ?>>Inter-Disciplinary Forum (IDF)</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select id="status" name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <option value="all" <?= request('status', 'all') == 'all' ? 'selected' : '' ?>>All Events</option>
                        <option value="active" <?= request('status') == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="upcoming" <?= request('status') == 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                        <option value="past" <?= request('status') == 'past' ? 'selected' : '' ?>>Past</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full btn-ripple bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-2.5 rounded-xl hover:from-indigo-600 hover:to-purple-700 shadow-medium hover:shadow-strong transition-all duration-200 font-medium">Apply Filters</button>
                </div>
            </div>

            <?php if ($activeSemester && !request('semester_id')): ?>
            <div class="mt-4 p-3 bg-blue-50 border-l-4 border-blue-500 rounded-lg">
                <p class="text-sm text-blue-800">
                    <span class="font-semibold">Showing events for:</span> <?= e($activeSemester->name) ?>
                    <span class="text-blue-600">(<?= e($activeSemester->start_date->format('M d, Y')) ?> - <?= e($activeSemester->end_date->format('M d, Y')) ?>)</span>
                </p>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php $__empty = true; foreach ($events as $event): $__empty = false; ?>
        <div class="group bg-white rounded-xl shadow-soft hover:shadow-strong transition-all duration-300 overflow-hidden card-hover">
            <div class="h-1.5 bg-gradient-to-r <?= $event->isActive() ? 'from-green-400 to-green-600' : ($event->start_time->isFuture() ? 'from-blue-400 to-blue-600' : 'from-gray-400 to-gray-600') ?>"></div>

            <div class="p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-gray-900 group-hover:text-indigo-600 transition-colors line-clamp-1"><?= e($event->name) ?></h3>
                    </div>
                    <?php if ($event->isActive()): ?>
                    <span class="ml-2 px-2 py-1 text-xs font-bold bg-gradient-to-r from-green-400 to-green-500 text-white rounded-lg shadow-soft animate-pulse">Active</span>
                    <?php elseif ($event->start_time->isFuture()): ?>
                    <span class="ml-2 px-2 py-1 text-xs font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white rounded-lg shadow-soft">Upcoming</span>
                    <?php else: ?>
                    <span class="ml-2 px-2 py-1 text-xs font-bold bg-gradient-to-r from-gray-400 to-gray-500 text-white rounded-lg shadow-soft">Past</span>
                    <?php endif; ?>
                </div>

                <div class="space-y-2 mb-3">
                    <div class="flex items-center text-xs text-gray-600">
                        <div class="flex items-center justify-center w-6 h-6 rounded-lg bg-purple-100 mr-2">
                            <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        </div>
                        <span class="font-medium"><?= e(ucfirst(str_replace('_', ' ', $event->type))) ?></span>
                    </div>

                    <div class="flex items-center text-xs text-gray-600">
                        <div class="flex items-center justify-center w-6 h-6 rounded-lg bg-blue-100 mr-2">
                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="font-medium"><?= e($event->start_time->format('M d, Y')) ?></p>
                            <p class="text-xs text-gray-500"><?= e($event->start_time->format('h:i A')) ?> - <?= e($event->end_time->format('h:i A')) ?></p>
                        </div>
                    </div>

                    <div class="flex items-center text-xs text-gray-600">
                        <div class="flex items-center justify-center w-6 h-6 rounded-lg bg-green-100 mr-2">
                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <span class="font-medium"><?= e($event->creator?->name) ?></span>
                    </div>

                    <?php if ($event->officers->count() > 0): ?>
                    <div class="flex items-start text-xs text-gray-600">
                        <div class="flex items-center justify-center w-6 h-6 rounded-lg bg-orange-100 mr-2 flex-shrink-0">
                            <svg class="w-3 h-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-xs text-gray-500">Officers:</p>
                            <p class="text-xs line-clamp-1"><?= e($event->officers->pluck('name')->join(', ')) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($event->description): ?>
                    <div class="pt-2 border-t border-gray-100">
                        <p class="text-xs text-gray-600 line-clamp-1"><?= e($event->description) ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="pt-3 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 shadow-soft">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div class="ml-2">
                                <p class="text-lg font-bold text-gray-900"><?= e($event->attendances->count()) ?></p>
                                <p class="text-xs text-gray-500">Attendees</p>
                            </div>
                        </div>
                        <a href="<?= route('attendance.index', ['event' => $event->id]) ?>" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg transition-all font-semibold text-xs flex items-center space-x-1">
                            <span>View</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    <?php if (auth()->user()->isAdmin()): ?>
                    <div class="flex space-x-2">
                        <a href="<?= route('events.edit', $event->id) ?>" class="flex-1 text-center bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-700 px-3 py-1.5 rounded-lg hover:from-indigo-100 hover:to-purple-100 transition-all text-xs font-semibold">Edit Event</a>
                        <form action="<?= route('events.destroy', $event->id) ?>" method="POST" class="flex-1">
                            <?= csrf_field() ?>
                            <?= method_field('DELETE') ?>
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this event?')" class="w-full bg-gradient-to-r from-red-50 to-pink-50 text-red-700 px-3 py-1.5 rounded-lg hover:from-red-100 hover:to-pink-100 transition-all text-xs font-semibold">Delete</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; if ($__empty): ?>
        <div class="col-span-full">
            <div class="bg-white rounded-2xl shadow-soft p-12 text-center">
                <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-lg font-semibold text-gray-900 mb-2">No events found</p>
                <p class="text-sm text-gray-500 mb-6">Try adjusting your filters or create a new event.</p>
                <?php if (auth()->user()->isAdmin()): ?>
                <a href="<?= route('events.create') ?>" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl hover:from-indigo-600 hover:to-purple-700 shadow-medium hover:shadow-strong transition-all font-semibold">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Create Your First Event
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($events->hasPages()): ?>
    <div class="bg-white rounded-2xl shadow-soft p-4"><?= $events->links() ?></div>
    <?php endif; ?>
</div>
<?php endsection(); ?>
