<?php layout('layouts.app'); ?>
<?php section('title', 'Mark Attendance - SAMS'); ?>

<?php section('content'); ?>
<div class="space-y-6" x-data="attendanceManager()">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Mark Attendance</h1>
    </div>

    <!-- Event Selection -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Select Active Event</h2>

        <?php if ($activeEvents->isEmpty()): ?>
        <div class="text-center py-8">
            <p class="text-gray-500">No active events at the moment.</p>
            <a href="<?= route('events.create') ?>" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800">Create New Event</a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($activeEvents as $event): ?>
            <div class="border rounded-lg p-4 cursor-pointer hover:border-indigo-500 transition-colors"
                 :class="selectedEventId === <?= $event->id ?> ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'"
                 @click="selectEvent(<?= $event->id ?>, '<?= e(addslashes($event->name)) ?>')">
                <h3 class="font-semibold text-gray-900"><?= e($event->name) ?></h3>
                <p class="text-sm text-gray-600 mt-1"><?= e(ucfirst(str_replace('_', ' ', $event->type))) ?></p>
                <p class="text-xs text-gray-500 mt-2"><?= e($event->start_time->format('M d, Y h:i A')) ?> - <?= e($event->end_time->format('h:i A')) ?></p>
                <p class="text-xs text-gray-500 mt-1">Created by: <?= e($event->creator?->name) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Event Details & Stats -->
    <div x-show="selectedEventId" x-cloak class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-white" x-text="selectedEventName"></h2>
                    <div class="flex items-center space-x-4 mt-2 text-white/90"><span class="text-sm">Event Statistics</span></div>
                </div>
            </div>
        </div>

        <div class="p-6 bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Students</p>
                            <p class="text-2xl font-bold text-gray-900" x-text="stats.totalStudents">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Present</p>
                            <p class="text-2xl font-bold text-green-600" x-text="stats.attendanceCount">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Attendance Rate</p>
                            <p class="text-2xl font-bold text-purple-600" x-text="stats.attendanceRate + '%'">0%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanner Section -->
    <div x-show="selectedEventId" x-cloak class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
        <div class="flex items-center space-x-3 mb-6">
            <svg class="w-8 h-8 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <h3 class="text-2xl font-bold text-gray-900">Card Scanners</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <button @click="startScanner('barcode')" class="flex items-center justify-center space-x-3 bg-blue-700 hover:bg-blue-800 text-white font-semibold py-4 px-6 rounded-xl transition-colors duration-200 shadow-md">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-lg">Scan Barcode/QR</span>
            </button>

            <button @click="captureIdPhoto()" class="relative flex items-center justify-center space-x-3 bg-green-600 hover:bg-green-700 text-white font-semibold py-4 px-6 rounded-xl transition-colors duration-200 shadow-md">
                <span class="absolute -top-2 -right-2 bg-amber-400 text-gray-900 text-[10px] font-bold px-2 py-0.5 rounded-full shadow">CLEAREST</span>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-lg">Capture ID Photo</span>
            </button>

            <button @click="startScanner('ocr')" class="flex items-center justify-center space-x-3 bg-blue-700 hover:bg-blue-800 text-white font-semibold py-4 px-6 rounded-xl transition-colors duration-200 shadow-md">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                <span class="text-lg">Scan ID Text (Live)</span>
            </button>
        </div>
        <p class="text-sm text-gray-500 mb-6">On a phone, <span class="font-semibold text-green-700">Capture ID Photo</span> opens your camera to take a sharp, high-resolution picture — the most reliable way to read an ID number.</p>

        <!-- Native camera / file input for high-resolution still capture -->
        <input type="file" accept="image/*" capture="environment" x-ref="idPhoto" @change="onIdPhotoSelected($event)" class="hidden">

        <!-- Captured-photo result -->
        <div x-show="captureMode" x-cloak class="mb-6">
            <div class="flex flex-col items-center bg-gray-50 border border-gray-200 rounded-xl p-4">
                <template x-if="capturePreview">
                    <img :src="capturePreview" alt="Captured ID" class="max-h-56 rounded-lg border border-gray-200 shadow mb-3">
                </template>
                <div x-show="captureBusy" class="flex items-center space-x-2 text-blue-600 font-semibold">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span x-text="captureStatus || 'Reading ID from photo…'"></span>
                </div>
                <div x-show="!captureBusy && captureStatus" class="text-center font-semibold" :class="{
                    'text-green-600': captureStatusType === 'success',
                    'text-orange-600': captureStatusType === 'warning',
                    'text-red-600': captureStatusType === 'error',
                    'text-blue-600': captureStatusType === 'info'
                }" x-text="captureStatus"></div>
                <button x-show="!captureBusy" @click="captureIdPhoto()" class="mt-3 inline-flex items-center space-x-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Capture another</span>
                </button>
            </div>
        </div>

        <div x-show="isScanning" x-cloak>
            <div class="relative">
                <div id="qr-reader" class="w-full max-w-2xl mx-auto rounded-lg overflow-hidden"></div>
                <div class="absolute top-4 right-4 flex items-center space-x-2 z-10">
                    <button x-show="torchAvailable" @click="toggleTorch()"
                            :class="torchOn ? 'bg-amber-400 text-gray-900' : 'bg-gray-800/80 text-white hover:bg-gray-900'"
                            class="p-2 rounded-lg shadow-lg transition-colors" title="Toggle flashlight">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </button>
                    <button @click="stopScanner()" class="bg-red-600 hover:bg-red-700 text-white p-2 rounded-lg shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <p x-show="scanMode === 'ocr'" class="absolute bottom-3 left-1/2 -translate-x-1/2 bg-black/60 text-white text-xs px-3 py-1.5 rounded-full z-10 whitespace-nowrap">Center the ID number in the frame &middot; hold steady</p>
            </div>
            <div x-show="scannerStatus" class="mt-4 text-center font-semibold text-lg" :class="{
                'text-blue-600': scannerStatusType === 'info',
                'text-green-600': scannerStatusType === 'success',
                'text-orange-600': scannerStatusType === 'warning',
                'text-red-600': scannerStatusType === 'error'
            }" x-text="scannerStatus"></div>
        </div>
    </div>

    <!-- Manual Entry -->
    <div x-show="selectedEventId" x-cloak class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
        <div class="flex items-center space-x-3 mb-6">
            <svg class="w-8 h-8 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <h3 class="text-2xl font-bold text-gray-900">Manual Entry</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-3">
            <input type="text" x-model="manualStudentId" @keyup.enter="markAttendanceManual()" placeholder="Enter Student ID (e.g. 223CS02003501)" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base sm:text-lg">
            <button @click="markAttendanceManual()" :disabled="loading" class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition-colors duration-200 shadow-md text-sm whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!loading">Verify</span>
                <span x-show="loading">...</span>
            </button>
        </div>
        <p class="mt-3 text-sm text-orange-500 font-medium">&#9888;&#65039; Manual entries are flagged for review</p>

        <div x-show="statusMessage" x-cloak class="mt-4">
            <div :class="{
                'bg-green-100 border-green-400 text-green-700': statusType === 'success',
                'bg-red-100 border-red-400 text-red-700': statusType === 'error',
                'bg-yellow-100 border-yellow-400 text-yellow-700': statusType === 'warning'
            }" class="p-4 border-2 rounded-lg">
                <p x-text="statusMessage" class="font-semibold"></p>
                <div x-show="studentInfo && statusType === 'success'" class="mt-3 pt-3 border-t" :class="{
                    'border-green-300': statusType === 'success',
                    'border-yellow-300': statusType === 'warning'
                }">
                    <p class="font-semibold" x-text="studentInfo?.first_name + ' ' + studentInfo?.last_name"></p>
                    <p class="text-sm" x-text="'ID: ' + studentInfo?.student_id"></p>
                    <p class="text-sm" x-text="'Department: ' + (studentInfo?.department || 'N/A')"></p>
                    <p class="text-sm" x-text="'Level: ' + studentInfo?.level"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance List -->
    <div x-show="selectedEventId" x-cloak class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-900">Attendance Records</h3>
                <div class="flex items-center space-x-2">
                    <input type="text" x-model="searchTerm" @input="filterAttendance()" placeholder="Search students..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Program</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="attendance in filteredAttendances" :key="attendance.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="attendance.student.student_id"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900" x-text="attendance.student.first_name + ' ' + attendance.student.last_name"></div>
                                <div class="text-sm text-gray-500" x-text="attendance.student.email"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="attendance.student.program?.name || 'N/A'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="formatTime(attendance.timestamp)"></td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Present</span></td>
                        </tr>
                    </template>
                    <tr x-show="filteredAttendances.length === 0">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <span x-show="attendances.length === 0">No attendance records yet. Start scanning QR codes to mark attendance.</span>
                            <span x-show="attendances.length > 0 && filteredAttendances.length === 0">No matching records found.</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endsection(); ?>

<?php push('scripts'); ?>
<!-- Libraries are self-hosted so the scanner works on offline / restricted
     networks. Each falls back to a CDN only if the local copy is missing. -->
<script>window.__tessBase = '<?= e(asset('vendor/tesseract')) ?>';</script>
<script src="<?= e(asset('vendor/html5-qrcode.min.js')) ?>"
        onerror="var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js';document.head.appendChild(s);"></script>
<script src="<?= e(asset('vendor/tesseract/tesseract.min.js')) ?>"
        onerror="window.__tessBase=null;var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/tesseract.js@4.1.4/dist/tesseract.min.js';document.head.appendChild(s);"></script>
<script>
/**
 * Turn a raw (often blurry / dim) camera frame into a clean, high-contrast
 * black-on-white image that OCR can read reliably.
 *
 *  1. Crop to the central guide region (where the operator places the ID).
 *  2. Upscale so small print gets more pixels for the recogniser.
 *  3. Grayscale + contrast stretch to fight uneven lighting.
 *  4. Otsu global thresholding to binarise text away from the background.
 */
function preprocessCardFrame(video) {
    const vw = video.videoWidth, vh = video.videoHeight;

    // Crop the central band that matches the on-screen guide box.
    const cropW = Math.round(vw * 0.82);
    const cropH = Math.round(cropW * (260 / 420)); // match qrbox aspect ratio
    const sx = Math.round((vw - cropW) / 2);
    const sy = Math.round((vh - Math.min(cropH, vh)) / 2);
    const sh = Math.min(cropH, vh);

    // Upscale the crop (cap width to keep OCR fast).
    const scale = Math.min(2.2, 1400 / cropW);
    const w = Math.round(cropW * scale);
    const h = Math.round(sh * scale);

    const canvas = document.createElement('canvas');
    canvas.width = w; canvas.height = h;
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(video, sx, sy, cropW, sh, 0, 0, w, h);

    const img = ctx.getImageData(0, 0, w, h);
    const px = img.data;
    const n = w * h;

    // Grayscale + build a histogram for Otsu's method.
    const gray = new Uint8ClampedArray(n);
    const hist = new Array(256).fill(0);
    let min = 255, max = 0;
    for (let i = 0, j = 0; i < px.length; i += 4, j++) {
        const g = (px[i] * 0.299 + px[i + 1] * 0.587 + px[i + 2] * 0.114) | 0;
        gray[j] = g;
        if (g < min) min = g;
        if (g > max) max = g;
    }
    // Contrast stretch to use the full 0..255 range.
    const range = Math.max(1, max - min);
    for (let j = 0; j < n; j++) {
        const s = ((gray[j] - min) * 255 / range) | 0;
        gray[j] = s;
        hist[s]++;
    }

    // Otsu threshold.
    let sum = 0;
    for (let t = 0; t < 256; t++) sum += t * hist[t];
    let sumB = 0, wB = 0, maxVar = -1, threshold = 128;
    for (let t = 0; t < 256; t++) {
        wB += hist[t];
        if (wB === 0) continue;
        const wF = n - wB;
        if (wF === 0) break;
        sumB += t * hist[t];
        const mB = sumB / wB;
        const mF = (sum - sumB) / wF;
        const between = wB * wF * (mB - mF) * (mB - mF);
        if (between > maxVar) { maxVar = between; threshold = t; }
    }

    // Binarise (with a small bias so thin strokes survive).
    const cut = threshold + 8;
    for (let i = 0, j = 0; i < px.length; i += 4, j++) {
        const v = gray[j] > cut ? 255 : 0;
        px[i] = px[i + 1] = px[i + 2] = v;
    }
    ctx.putImageData(img, 0, 0);
    return canvas;
}

/** Load a selected File into a fully-decoded HTMLImageElement. */
function loadImageFromFile(file) {
    return new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => {
            if (img.decode) {
                img.decode().then(() => resolve(img)).catch(() => resolve(img)).finally(() => URL.revokeObjectURL(url));
            } else {
                URL.revokeObjectURL(url);
                resolve(img);
            }
        };
        img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('image load failed')); };
        img.src = url;
    });
}

/**
 * Preprocess a full still photo of an ID card for OCR. Unlike the live-frame
 * version this keeps (almost) the whole framed image, scales it to a good OCR
 * resolution, then either binarises (mode 'binary') or just boosts contrast in
 * grayscale (mode 'gray').
 */
function preprocessCardImage(img, mode) {
    mode = mode || 'binary';
    const iw = img.naturalWidth || img.width;
    const ih = img.naturalHeight || img.height;

    const cropW = Math.round(iw * 0.98);
    const cropH = Math.round(ih * 0.98);
    const sx = Math.round((iw - cropW) / 2);
    const sy = Math.round((ih - cropH) / 2);

    // Normalise to ~2200px wide: upscale small photos (so tiny ID digits get
    // enough pixels), shrink oversized ones.
    const scale = Math.min(3.0, Math.max(0.3, 2200 / cropW));
    const w = Math.round(cropW * scale);
    const h = Math.round(cropH * scale);

    const canvas = document.createElement('canvas');
    canvas.width = w; canvas.height = h;
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(img, sx, sy, cropW, cropH, 0, 0, w, h);

    // 'raw' = just the scaled colour image (some cards read better untouched).
    if (mode === 'raw') { return canvas; }

    const imgData = ctx.getImageData(0, 0, w, h);
    const px = imgData.data;
    const n = w * h;

    const gray = new Uint8ClampedArray(n);
    const hist = new Array(256).fill(0);
    let min = 255, max = 0;
    for (let i = 0, j = 0; i < px.length; i += 4, j++) {
        const g = (px[i] * 0.299 + px[i + 1] * 0.587 + px[i + 2] * 0.114) | 0;
        gray[j] = g;
        if (g < min) min = g;
        if (g > max) max = g;
    }
    const range = Math.max(1, max - min);
    for (let j = 0; j < n; j++) {
        const s = ((gray[j] - min) * 255 / range) | 0;
        gray[j] = s;
        hist[s]++;
    }

    if (mode === 'gray') {
        for (let i = 0, j = 0; i < px.length; i += 4, j++) { px[i] = px[i + 1] = px[i + 2] = gray[j]; }
        ctx.putImageData(imgData, 0, 0);
        return canvas;
    }

    // Otsu threshold.
    let sum = 0;
    for (let t = 0; t < 256; t++) sum += t * hist[t];
    let sumB = 0, wB = 0, maxVar = -1, threshold = 128;
    for (let t = 0; t < 256; t++) {
        wB += hist[t];
        if (wB === 0) continue;
        const wF = n - wB;
        if (wF === 0) break;
        sumB += t * hist[t];
        const mB = sumB / wB;
        const mF = (sum - sumB) / wF;
        const between = wB * wF * (mB - mF) * (mB - mF);
        if (between > maxVar) { maxVar = between; threshold = t; }
    }
    const cut = threshold + 8;
    for (let i = 0, j = 0; i < px.length; i += 4, j++) {
        const v = gray[j] > cut ? 255 : 0;
        px[i] = px[i + 1] = px[i + 2] = v;
    }
    ctx.putImageData(imgData, 0, 0);
    return canvas;
}

function attendanceManager() {
    return {
        selectedEventId: <?= $selectedEvent->id ?? 'null' ?>,
        selectedEventName: '<?= e(addslashes($selectedEvent->name ?? '')) ?>',
        manualStudentId: '',
        loading: false,
        statusMessage: '',
        statusType: '',
        studentInfo: null,
        isScanning: false,
        scanMode: null,
        scannerStatus: '',
        scannerStatusType: 'info',
        html5QrCode: null,
        lastScannedId: null,
        lastScanTime: 0,
        scanCooldown: 3000,
        ocrInterval: null,
        ocrWorker: null,
        ocrBusy: false,
        ocrCandidates: {},
        ocrTicks: 0,
        torchAvailable: false,
        torchOn: false,
        captureMode: false,
        capturePreview: null,
        captureBusy: false,
        captureStatus: '',
        captureStatusType: 'info',
        attendances: <?= json_encode($attendances ? $attendances->items() : []) ?>,
        filteredAttendances: <?= json_encode($attendances ? $attendances->items() : []) ?>,
        searchTerm: '',
        stats: {
            totalStudents: <?= (int) ($totalStudents ?? 0) ?>,
            attendanceCount: <?= (int) ($attendanceCount ?? 0) ?>,
            attendanceRate: <?= $totalStudents > 0 ? round(($attendanceCount / $totalStudents) * 100, 1) : 0 ?>
        },

        selectEvent(eventId, eventName) {
            this.selectedEventId = eventId;
            this.selectedEventName = eventName;
            this.statusMessage = '';
            window.location.href = `<?= route('attendance.index') ?>?event=${eventId}`;
        },

        async startScanner(mode) {
            this.scanMode = mode;
            this.isScanning = true;
            this.scannerStatus = '';
            this.torchAvailable = false;
            this.torchOn = false;
            this.ocrCandidates = {};
            this.ocrTicks = 0;
            await this.$nextTick();

            // Camera APIs only work in a secure context (HTTPS or localhost).
            // Accessing the app over http://<LAN-IP> — common when testing on a
            // phone — silently disables the camera, so flag it explicitly.
            if (!window.isSecureContext || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                const host = location.hostname;
                const msg = (host === 'localhost' || host === '127.0.0.1')
                    ? 'Camera unavailable in this browser. Try Chrome or Edge.'
                    : `Camera blocked: this page is not on a secure (HTTPS) connection. Open it via https://, or on this device use http://localhost. (Current: ${location.protocol}//${host})`;
                this.showStatus(msg, 'error');
                this.updateScannerStatus('Secure connection (HTTPS) required for camera', 'error');
                this.isScanning = false;
                return;
            }

            // Warm up OCR first so its (network) failures aren't misreported as a
            // camera problem.
            if (mode === 'ocr') {
                this.updateScannerStatus('Warming up text recognition...', 'info');
                try {
                    await this.ensureOcrWorker();
                } catch (err) {
                    console.error('OCR init error:', err);
                    this.showStatus('Could not load the text-recognition engine. Check your internet connection and try again.', 'error');
                    this.updateScannerStatus('Text-recognition engine failed to load', 'error');
                    this.stopScanner();
                    return;
                }
            }

            try {
                this.updateScannerStatus('Starting camera... Please allow camera access.', 'info');
                const scanConfig = mode === 'barcode'
                    ? { fps: 10, qrbox: { width: 300, height: 300 }, aspectRatio: 1.0 }
                    : { fps: 5, qrbox: { width: 420, height: 260 } };
                const onDecode = mode === 'barcode'
                    ? (decodedText) => this.processScannedData(decodedText, 'scan')
                    : () => {};

                await this.startCamera(scanConfig, onDecode);
                this.detectTorch();

                if (mode === 'barcode') {
                    this.updateScannerStatus('Ready to scan QR codes or barcodes', 'success');
                } else {
                    this.updateScannerStatus('Align the ID number inside the frame and hold steady.', 'info');
                    this.startOcrProcessing();
                }
            } catch (err) {
                console.error('Scanner error:', err);
                const msg = this.cameraErrorMessage(err);
                this.showStatus(msg, 'error');
                this.updateScannerStatus(msg, 'error');
                this.stopScanner();
            }
        },

        // Start the camera at high resolution, falling back to progressively
        // simpler constraints if the device can't satisfy them (older phones,
        // front-only cameras, over-constrained errors).
        async startCamera(scanConfig, onDecode) {
            const attempts = [
                { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 }, advanced: [{ focusMode: 'continuous' }] },
                { facingMode: 'environment' },
                { facingMode: 'user' },
                true // let the browser/library pick any available camera
            ];

            let lastErr = null;
            for (const constraint of attempts) {
                try {
                    this.html5QrCode = new Html5Qrcode("qr-reader");
                    await this.html5QrCode.start(constraint, scanConfig, onDecode, () => {});
                    return; // success
                } catch (err) {
                    lastErr = err;
                    // Only fall through for constraint/availability issues; a hard
                    // permission denial won't be fixed by relaxing constraints.
                    if (err && (err.name === 'NotAllowedError' || err.name === 'SecurityError')) throw err;
                    try { if (this.html5QrCode) { await this.html5QrCode.clear(); } } catch (e) {}
                    this.html5QrCode = null;
                }
            }
            throw lastErr;
        },

        cameraErrorMessage(err) {
            const name = err && err.name ? err.name : '';
            switch (name) {
                case 'NotAllowedError':
                case 'PermissionDeniedError':
                    return 'Camera permission was denied. Enable camera access for this site in your browser settings, then try again.';
                case 'NotFoundError':
                case 'DevicesNotFoundError':
                    return 'No camera was found on this device.';
                case 'NotReadableError':
                case 'TrackStartError':
                    return 'The camera is already in use by another app. Close it and try again.';
                case 'OverconstrainedError':
                    return 'This camera could not meet the requested settings. Try again or use manual entry.';
                case 'SecurityError':
                    return 'Camera blocked for security reasons. The page must be served over HTTPS (or localhost).';
                default:
                    return 'Unable to access the camera. Please check permissions and that no other app is using it.';
            }
        },

        stopScanner() {
            this.isScanning = false;
            this.torchAvailable = false;
            this.torchOn = false;
            if (this.ocrInterval) { clearInterval(this.ocrInterval); this.ocrInterval = null; }
            if (this.html5QrCode) {
                this.html5QrCode.stop().then(() => { this.html5QrCode.clear(); this.html5QrCode = null; }).catch(err => console.error('Error stopping scanner:', err));
            }
            if (this.ocrWorker) {
                this.ocrWorker.terminate().catch(() => {});
                this.ocrWorker = null;
            }
            this.scannerStatus = '';
        },

        // Create a single, reusable Tesseract worker tuned for ID codes.
        // Prefer self-hosted assets; fall back to the CDN if they're missing.
        async ensureOcrWorker() {
            if (this.ocrWorker) return;
            if (typeof Tesseract === 'undefined') {
                throw new Error('Tesseract library not loaded');
            }
            const base = window.__tessBase;
            const localOpts = base ? {
                workerPath: base + '/worker.min.js',
                corePath:   base + '/',
                langPath:   base + '/'
            } : {};

            // tesseract.js v4.x: createWorker(options) does NOT auto-initialize.
            // The language must be loaded and initialized explicitly before the
            // worker's OCR API exists (otherwise setParameters/recognize throw).
            const buildWorker = async (opts) => {
                const w = await Tesseract.createWorker(opts);
                await w.loadLanguage('eng');
                await w.initialize('eng', 1); // OEM 1 = LSTM engine
                return w;
            };

            let worker;
            try {
                worker = await buildWorker(localOpts);
            } catch (e) {
                console.warn('Local OCR assets unavailable, trying CDN...', e);
                worker = await buildWorker({}); // CDN defaults
            }

            const psm = (window.Tesseract && Tesseract.PSM && Tesseract.PSM.SPARSE_TEXT) || '11';
            await worker.setParameters({
                tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ',
                tessedit_pageseg_mode: psm,
                user_defined_dpi: '300'
            });
            this.ocrWorker = worker;
        },

        // Detect and expose a torch/flashlight control when the device supports it.
        detectTorch() {
            try {
                const caps = this.html5QrCode.getRunningTrackCapabilities();
                this.torchAvailable = !!(caps && caps.torch);
            } catch (e) { this.torchAvailable = false; }
        },

        async toggleTorch() {
            if (!this.html5QrCode || !this.torchAvailable) return;
            this.torchOn = !this.torchOn;
            try {
                await this.html5QrCode.applyVideoConstraints({ advanced: [{ torch: this.torchOn }] });
            } catch (e) {
                console.error('Torch error:', e);
                this.torchOn = false;
                this.torchAvailable = false;
            }
        },

        startOcrProcessing() {
            // Persistent worker means we can poll more often; a busy-flag prevents
            // overlapping recognitions from stacking up on slower devices.
            this.ocrInterval = setInterval(() => this.runOcrOnce(), 1000);
        },

        async runOcrOnce() {
            if (!this.isScanning || this.scanMode !== 'ocr') { clearInterval(this.ocrInterval); this.ocrInterval = null; return; }
            if (this.ocrBusy || !this.ocrWorker) return;
            const video = document.querySelector('#qr-reader video');
            if (!video || !video.videoWidth) return;

            this.ocrBusy = true;
            try {
                const canvas = preprocessCardFrame(video);
                const { data } = await this.ocrWorker.recognize(canvas);
                const text = (data && data.text) || '';
                const confidence = (data && data.confidence) || 0;
                this.ocrTicks++;

                const studentId = this.extractStudentId(text);
                if (!studentId) {
                    // Give the operator actionable coaching instead of a dead frame.
                    if (this.ocrTicks % 2 === 0) {
                        const hint = confidence < 45
                            ? 'Text is unclear — move closer, steady the card, or add light.'
                            : 'Searching for a valid ID number... keep the card in frame.';
                        this.updateScannerStatus(hint, 'warning');
                    }
                    return;
                }

                // Consensus: require the same ID on two reads (or one very confident
                // read) so a single blurry misread never marks the wrong student.
                this.ocrCandidates[studentId] = (this.ocrCandidates[studentId] || 0) + 1;
                const seen = this.ocrCandidates[studentId];
                if (seen >= 2 || confidence >= 80) {
                    this.ocrCandidates = {};
                    this.updateScannerStatus(`Detected ID: ${studentId} — verifying...`, 'info');
                    this.processScannedData(studentId, 'ocr_scan');
                } else {
                    this.updateScannerStatus(`Reading ${studentId}... hold steady to confirm.`, 'info');
                }
            } catch (err) {
                console.error('OCR error:', err);
            } finally {
                this.ocrBusy = false;
            }
        },

        // --- High-resolution still capture (native camera on phones) ---------
        captureIdPhoto() {
            if (!this.selectedEventId) { this.showStatus('Please select an event first.', 'error'); return; }
            // Stop the live scanner if it's running so the camera is free.
            if (this.isScanning) { this.stopScanner(); }
            this.captureStatus = '';
            // Reset so re-selecting the same file still fires @change.
            if (this.$refs.idPhoto) { this.$refs.idPhoto.value = ''; this.$refs.idPhoto.click(); }
        },

        async onIdPhotoSelected(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) return;

            this.captureMode = true;
            this.captureBusy = true;
            if (this.capturePreview) { try { URL.revokeObjectURL(this.capturePreview); } catch (e) {} }
            this.capturePreview = URL.createObjectURL(file);
            this.captureStatus = 'Reading ID from photo…';
            this.captureStatusType = 'info';

            try {
                await this.ensureOcrWorker();
            } catch (err) {
                console.error('OCR init error:', err);
                this.captureBusy = false;
                this.captureStatus = 'Could not load the text-recognition engine. Check your connection and try again.';
                this.captureStatusType = 'error';
                return;
            }

            let img;
            try {
                img = await loadImageFromFile(file);
            } catch (err) {
                this.captureBusy = false;
                this.captureStatus = 'Could not read that image. Please try again.';
                this.captureStatusType = 'error';
                return;
            }

            const studentId = await this.recognizeCard(img);

            this.captureBusy = false;

            if (!studentId) {
                this.captureStatus = 'No ID number found. Move closer so the ID number fills most of the frame, keep it flat and in focus, avoid glare — then capture again.';
                this.captureStatusType = 'error';
                this.playSound('error');
                return;
            }

            this.captureStatus = `Detected ID: ${studentId} — marking attendance…`;
            this.captureStatusType = 'success';
            // Bypass the live-scan cooldown for deliberate captures.
            this.lastScannedId = null;
            this.markAttendance(studentId, 'ocr_scan');
        },

        // Run OCR over several preprocessing + page-segmentation combinations,
        // returning as soon as one yields a valid ID. This is what lets a single
        // captured photo read reliably across lighting/quality differences.
        async recognizeCard(img) {
            const PSM = (window.Tesseract && Tesseract.PSM) ? Tesseract.PSM : {};
            const passes = [
                { mode: 'binary', psm: PSM.SPARSE_TEXT   || '11' },
                { mode: 'gray',   psm: PSM.SPARSE_TEXT   || '11' },
                { mode: 'binary', psm: PSM.SINGLE_BLOCK  || '6'  },
                { mode: 'gray',   psm: PSM.SINGLE_BLOCK  || '6'  },
                { mode: 'raw',    psm: PSM.SINGLE_BLOCK  || '6'  },
                { mode: 'binary', psm: PSM.SINGLE_LINE   || '7'  },
                { mode: 'raw',    psm: PSM.AUTO          || '3'  }
            ];

            // Cache each preprocessed canvas so we don't rebuild it per PSM.
            const canvases = {};
            let found = null;
            try {
                for (const p of passes) {
                    if (!canvases[p.mode]) { canvases[p.mode] = preprocessCardImage(img, p.mode); }
                    await this.ocrWorker.setParameters({ tessedit_pageseg_mode: p.psm });
                    const { data } = await this.ocrWorker.recognize(canvases[p.mode]);
                    const id = this.extractStudentId((data && data.text) || '');
                    if (id) { found = id; break; }
                }
            } catch (err) {
                console.error('OCR pass failed', err);
            } finally {
                // Restore the default sparse mode for the live scanner.
                try { await this.ocrWorker.setParameters({ tessedit_pageseg_mode: PSM.SPARSE_TEXT || '11' }); } catch (e) {}
            }
            return found;
        },

        processScannedData(scannedText, method = 'scan') {
            const now = Date.now();
            let studentId = scannedText;
            if (method === 'ocr_scan') {
                // Already extracted upstream, but stay defensive.
                studentId = this.extractStudentId(scannedText) || scannedText;
            }
            if (studentId === this.lastScannedId && (now - this.lastScanTime) < this.scanCooldown) { return; }
            this.lastScannedId = studentId;
            this.lastScanTime = now;
            this.markAttendance(studentId, method);
        },

        extractStudentId(text) {
            if (!text) return null;
            // Flatten to a single A-Z0-9 stream (the number often wraps or sits
            // next to other card text, and OCR sprinkles spaces/newlines).
            const s = text.toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (s.length < 8) return null;

            // The VVU ID card format is fixed: 3 digits + 2 letters + 8 digits
            // (e.g. 223EI02003543, 223CS02003501). Treat it as authoritative so
            // an ambiguous character (a 0 misread as O, etc.) is resolved in
            // favour of this shape rather than a coincidental different grouping.
            const primary = this.bestWindowForShape(s, [3, 2, 8]);
            if (primary) return primary.value;

            // Only if no 3+2+8 window exists at all, consider near shapes for
            // other cohorts, preferring the fewest corrections.
            const shapes = [[3, 3, 8], [3, 3, 7], [3, 2, 7], [2, 2, 8], [2, 3, 7]];
            let best = null;
            for (const shape of shapes) {
                const c = this.bestWindowForShape(s, shape);
                if (c && (!best || c.coercions < best.coercions ||
                         (c.coercions === best.coercions && c.total > best.total))) {
                    best = c;
                }
            }
            if (best) return best.value;

            // Fallback: rigid patterns that still require embedded letters.
            return this.matchIdPatterns(s);
        },

        // For one [digits, letters, digits] shape, find the window that fits with
        // the fewest character corrections. Each position is coerced to what it
        // must be (digit vs letter), fixing the swaps OCR makes most often. A
        // real letter in the letter block is required so plain numbers elsewhere
        // on the card can't masquerade as an ID.
        bestWindowForShape(s, shape) {
            const d1 = shape[0], ln = shape[1], d2 = shape[2];
            const total = d1 + ln + d2;
            if (s.length < total) return null;

            // Conservative digit coercion: only letters OCR genuinely confuses
            // with digits. Coercing common word letters (A, L, T, G...) would let
            // words like "CALL" masquerade as an ID number.
            const toDigit = { O: '0', Q: '0', I: '1', S: '5', B: '8', Z: '2' };
            const toLetter = { '0': 'O', '1': 'I', '2': 'Z', '4': 'A', '5': 'S', '6': 'G', '7': 'T', '8': 'B' };
            const asDigit = (c) => (c >= '0' && c <= '9') ? c : (toDigit[c] || null);
            const asLetter = (c) => (c >= 'A' && c <= 'Z') ? c : (toLetter[c] || null);

            let best = null;
            for (let i = 0; i + total <= s.length; i++) {
                let out = '', realLetters = 0, coercions = 0, ok = true;
                for (let k = 0; k < total; k++) {
                    const c = s[i + k];
                    let m;
                    if (k < d1 || k >= d1 + ln) {
                        m = asDigit(c);
                        if (m !== null && !(c >= '0' && c <= '9')) coercions++;
                    } else {
                        m = asLetter(c);
                        if (c >= 'A' && c <= 'Z') realLetters++;
                        else if (m !== null) coercions++;
                    }
                    if (m === null) { ok = false; break; }
                    out += m;
                }
                if (ok && realLetters >= 1 && (!best || coercions < best.coercions)) {
                    best = { value: out, coercions: coercions, total: total };
                }
            }
            return best;
        },

        matchIdPatterns(text) {
            // These all require embedded letters, so a bare phone/date number
            // can't be mistaken for a student ID.
            let match = text.match(/(\d{2,3}[A-Z]{2,4}\d{2}\d{6})/);
            if (match) return match[1];
            match = text.match(/(\d{2,3}[-\s]?[A-Z]{2,4}[-\s]?\d{2}[-\s]?\d{6})/);
            if (match) return match[1].replace(/[-\s]/g, '');
            match = text.match(/([A-Z]*\d{2,3}[A-Z]{2,4}\d{8,})/);
            if (match) return match[1];
            return null;
        },

        async markAttendanceManual() {
            if (!this.manualStudentId.trim()) { this.showStatus('Please enter a Student ID', 'error'); return; }
            await this.markAttendance(this.manualStudentId.trim(), 'manual');
            this.manualStudentId = '';
        },

        async markAttendance(studentId, method = 'scan') {
            if (!this.selectedEventId || !studentId) { this.showStatus('Please select an event and enter student ID', 'error'); return; }
            this.loading = true;
            try {
                const response = await fetch('<?= route('attendance.mark') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?= e(csrf_token()) ?>', 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ event_id: this.selectedEventId, student_id: studentId, method: method })
                });
                const data = await response.json();
                if (data.success) {
                    const studentName = `${data.student.first_name} ${data.student.last_name}`;
                    this.showStatus(`✓ Attendance marked for ${studentName} (${data.student.student_id})`, 'success');
                    this.updateScannerStatus(`Success! ${studentName} marked present.`, 'success');
                    this.studentInfo = data.student;
                    this.stats.attendanceCount = data.total_marked;
                    this.stats.attendanceRate = this.stats.totalStudents > 0 ? ((data.total_marked / this.stats.totalStudents) * 100).toFixed(1) : 0;
                    this.attendances.unshift({ id: data.attendance.id, student: data.student, timestamp: new Date().toISOString() });
                    this.filterAttendance();
                    this.playSound('success');
                    setTimeout(() => { this.statusMessage = ''; }, 2000);
                } else {
                    if (data.status === 'ALREADY_RECORDED') {
                        const studentName = `${data.student.first_name} ${data.student.last_name}`;
                        this.showStatus(`⚠️ ${studentName} already marked present`, 'warning');
                        this.updateScannerStatus('Already recorded. Scan next card.', 'warning');
                        this.studentInfo = data.student;
                    } else {
                        this.showStatus(data.message || 'Error marking attendance', 'error');
                        this.updateScannerStatus(data.message || 'Error occurred', 'error');
                    }
                    this.playSound('error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showStatus('Network error: ' + error.message, 'error');
                this.updateScannerStatus('Network error. Check connection.', 'error');
                this.playSound('error');
            } finally {
                this.loading = false;
            }
        },

        showStatus(message, type) {
            this.statusMessage = message;
            this.statusType = type;
            setTimeout(() => { if (type !== 'success') { this.statusMessage = ''; } }, 5000);
        },

        updateScannerStatus(message, type = 'info') { this.scannerStatus = message; this.scannerStatusType = type; },

        playSound(type) {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            oscillator.connect(gainNode); gainNode.connect(audioContext.destination);
            oscillator.frequency.value = type === 'success' ? 800 : 400;
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.2);
        },

        filterAttendance() {
            if (!this.searchTerm) { this.filteredAttendances = this.attendances; return; }
            const term = this.searchTerm.toLowerCase();
            this.filteredAttendances = this.attendances.filter(att => {
                const student = att.student;
                return student.student_id.toLowerCase().includes(term) ||
                       student.first_name.toLowerCase().includes(term) ||
                       student.last_name.toLowerCase().includes(term) ||
                       (student.email && student.email.toLowerCase().includes(term)) ||
                       (student.program?.name && student.program.name.toLowerCase().includes(term));
            });
        },

        formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        }
    }
}
</script>
<?php endpush(); ?>
