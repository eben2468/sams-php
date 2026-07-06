<?php
use Carbon\Carbon;

$uniqueStudents = $attendances->unique('student_id')->count();
$uniqueEvents   = $attendances->unique('event_id')->count();

$byDepartment = $attendances->groupBy(fn ($item) => $item->student?->department?->name ?? 'Unknown')
    ->map(fn ($group) => $group->count())->sortDesc();

$byMethod = $attendances->groupBy('method')
    ->map(fn ($group) => $group->count())->sortDesc();

// Collections are already sorted descending, so first() is the largest count.
$maxDept   = $byDepartment->first() ?: 1;
$maxMethod = $byMethod->first() ?: 1;

/* ---- Institutional branding (from system settings) ------------------- */
$appName     = \App\Models\SystemSetting::get('app_name', 'SAMS');
$institution = 'Valley View University';

$logoData = null;
$logoRel  = \App\Models\SystemSetting::get('logo');
if ($logoRel) {
    $logoPath = dirname(__DIR__, 3) . '/public/uploads/' . ltrim($logoRel, '/');
    if (is_file($logoPath)) {
        $ext  = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        $mime = $ext === 'png' ? 'image/png' : ($ext === 'gif' ? 'image/gif' : 'image/jpeg');
        $logoData = 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($logoPath));
    }
}

/* ---- Document metadata ----------------------------------------------- */
$refNo = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $appName) ?: 'SAMS', 0, 4))
    . '/RPT/' . Carbon::parse($date)->format('Y') . '/'
    . strtoupper(substr(hash('crc32b', $date . $total), 0, 6));

$generatedBy = 'System Administrator';
try {
    $u = \App\Core\Auth::user();
    if ($u && !empty($u->name)) {
        $generatedBy = $u->name;
    }
} catch (\Throwable $e) {
    // fall back to default label
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= e($title) ?></title>
    <style>
        /* ================================================================
           Page setup
           ================================================================ */
        /* Colored header/footer bands run full-bleed (no horizontal page
           margin); flowing content is inset via the body padding below so
           nothing is clipped at the edges in either PDF or browser preview. */
        @page { margin: 158px 0 96px 0; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #263041;
            line-height: 1.5;
            padding: 0 48px;
        }

        /* ================================================================
           Repeating header
           ================================================================ */
        .doc-header {
            position: fixed;
            top: -138px;
            left: 0;
            right: 0;
            height: 128px;
        }
        .header-band {
            width: 100%;
            background-color: #0f2350;
            border-collapse: collapse;
        }
        .header-band td { padding: 20px 48px; vertical-align: middle; }

        .brand-logo {
            width: 58px;
            height: 58px;
            background-color: #ffffff;
            color: #0f2350;
            font-size: 27pt;
            font-weight: bold;
            text-align: center;
            line-height: 58px;
        }
        .brand-logo img { width: 58px; height: 58px; }

        .brand-name { color: #ffffff; font-size: 17pt; font-weight: bold; letter-spacing: 0.4px; }
        .brand-sub  { color: #aebbd6; font-size: 8pt; letter-spacing: 1.4px; padding-top: 3px; }

        .header-right { text-align: right; }
        .header-eyebrow { color: #c9a227; font-size: 7.5pt; font-weight: bold; letter-spacing: 1.6px; }
        .header-title { color: #ffffff; font-size: 15pt; font-weight: bold; padding-top: 2px; }
        .header-date  { color: #aebbd6; font-size: 7.5pt; padding-top: 4px; }

        .accent-bar { height: 5px; background-color: #c9a227; }
        .accent-line { height: 2px; background-color: #0f2350; }

        /* ================================================================
           Repeating footer
           ================================================================ */
        .doc-footer {
            position: fixed;
            bottom: -66px;
            left: 0;
            right: 0;
            height: 56px;
            padding: 9px 48px 0 48px;
            border-top: 2px solid #0f2350;
            font-size: 7.5pt;
            color: #6b7688;
        }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-table td { vertical-align: top; }
        .footer-center { text-align: center; }
        .footer-right { text-align: right; }
        .footer-strong { color: #0f2350; font-weight: bold; }
        .pagenum:after { content: "Page " counter(page) " of " counter(pages); }

        /* ================================================================
           Document title block (first page)
           ================================================================ */
        .doc-title-block {
            border: 1px solid #d7dded;
            border-top: 4px solid #0f2350;
            background-color: #f7f9fd;
            padding: 16px 20px;
            margin-bottom: 20px;
        }
        .doc-title-eyebrow { font-size: 7.5pt; font-weight: bold; letter-spacing: 2px; color: #c9a227; }
        .doc-title-main { font-size: 19pt; font-weight: bold; color: #0f2350; padding-top: 3px; }
        .doc-title-rule { height: 1px; background-color: #d7dded; margin: 12px 0; }
        .doc-title-meta { width: 100%; border-collapse: collapse; }
        .doc-title-meta td { width: 33.33%; vertical-align: top; padding-right: 14px; }
        .dt-label { font-size: 6.8pt; text-transform: uppercase; letter-spacing: 0.8px; color: #8a94a6; }
        .dt-value { font-size: 9pt; font-weight: bold; color: #263041; padding-top: 1px; }

        /* ================================================================
           Parameters strip
           ================================================================ */
        .meta-strip {
            width: 100%;
            border: 1px solid #e2e7f0;
            border-collapse: collapse;
            margin-bottom: 22px;
        }
        .meta-strip td {
            padding: 10px 14px;
            font-size: 8.5pt;
            border-right: 1px solid #e2e7f0;
            background-color: #ffffff;
        }
        .meta-strip td:last-child { border-right: none; }
        .meta-label { color: #8a94a6; text-transform: uppercase; letter-spacing: 0.6px; font-size: 6.8pt; display: block; margin-bottom: 3px; }
        .meta-value { color: #0f2350; font-weight: bold; }

        /* ================================================================
           Section headings
           ================================================================ */
        .section { margin-bottom: 26px; }
        .section-head { width: 100%; border-collapse: collapse; margin-bottom: 11px; }
        .section-head td { vertical-align: middle; }
        .section-tick { width: 5px; background-color: #c9a227; }
        .section-title {
            font-size: 11.5pt;
            font-weight: bold;
            color: #0f2350;
            padding-left: 9px;
            letter-spacing: 0.3px;
        }
        .section-hint { text-align: right; font-size: 7.5pt; color: #8a94a6; text-transform: uppercase; letter-spacing: 0.6px; }

        /* ================================================================
           Executive summary cards
           ================================================================ */
        .summary { width: 100%; border-collapse: separate; border-spacing: 11px 0; margin-bottom: 8px; }
        .summary td {
            width: 33.33%;
            background-color: #ffffff;
            border: 1px solid #d7dded;
            border-top: 3px solid #0f2350;
            padding: 15px 14px;
        }
        .card-label { font-size: 7pt; color: #8a94a6; text-transform: uppercase; letter-spacing: 0.7px; font-weight: bold; }
        .card-value { font-size: 24pt; font-weight: bold; color: #0f2350; padding-top: 3px; }
        .card-foot  { font-size: 7pt; color: #8a94a6; padding-top: 2px; }

        /* ================================================================
           Data tables
           ================================================================ */
        table.data { width: 100%; border-collapse: collapse; border: 1px solid #d7dded; }
        table.data thead { background-color: #0f2350; }
        table.data thead th {
            color: #ffffff;
            padding: 9px 8px;
            text-align: left;
            font-size: 7.2pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.data tbody td { padding: 7px 8px; font-size: 8.5pt; color: #3a4457; border-bottom: 1px solid #eef1f7; }
        table.data tbody tr:nth-child(even) { background-color: #f7f9fd; }
        table.data tfoot td {
            padding: 8px; font-size: 8.5pt; font-weight: bold;
            color: #0f2350; background-color: #eef2f9; border-top: 2px solid #0f2350;
        }
        .num { color: #a2abbb; font-weight: bold; }
        .strong { color: #0f2350; font-weight: bold; }
        .text-right { text-align: right; }

        /* ---- Proportional bars ------------------------------------------ */
        .bar-track { width: 100%; height: 9px; background-color: #eef1f7; }
        .bar-fill  { height: 9px; background-color: #0f2350; }
        .bar-fill-gold { height: 9px; background-color: #c9a227; }

        /* ================================================================
           Badges
           ================================================================ */
        .badge { display: inline-block; padding: 2px 8px; font-size: 6.8pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-info    { background-color: #dbeafe; color: #1e40af; }

        .no-data { text-align: center; padding: 30px; color: #a2abbb; font-style: italic; border: 1px dashed #d7dded; background-color: #fafbfe; }

        /* ================================================================
           Certification / signatures
           ================================================================ */
        .cert-note {
            border: 1px solid #d7dded;
            border-left: 4px solid #c9a227;
            background-color: #f7f9fd;
            padding: 11px 14px;
            font-size: 8pt;
            color: #5a6577;
            margin-bottom: 22px;
        }
        .cert-note strong { color: #0f2350; }
        .sign-table { width: 100%; border-collapse: separate; border-spacing: 24px 0; }
        .sign-table td { width: 50%; vertical-align: top; }
        .sign-line { border-top: 1px solid #97a1b3; padding-top: 5px; }
        .sign-role { font-size: 8.5pt; font-weight: bold; color: #0f2350; }
        .sign-name { font-size: 8pt; color: #5a6577; padding-top: 1px; }
        .sign-blank { height: 40px; }
    </style>
</head>
<body>

    <!-- Repeating header -->
    <div class="doc-header">
        <table class="header-band">
            <tr>
                <td style="width: 58px;">
                    <div class="brand-logo">
                        <?php if ($logoData): ?>
                            <img src="<?= $logoData ?>" alt="Logo">
                        <?php else: ?>
                            <?= e(strtoupper(substr($institution, 0, 1))) ?>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <div class="brand-name"><?= e($institution) ?></div>
                    <div class="brand-sub"><?= e(strtoupper($appName)) ?> &middot; STUDENT ATTENDANCE MANAGEMENT SYSTEM</div>
                </td>
                <td class="header-right">
                    <div class="header-eyebrow">OFFICIAL REPORT</div>
                    <div class="header-title"><?= e($title) ?></div>
                    <div class="header-date">Generated <?= e(Carbon::parse($date)->format('F d, Y \a\t h:i A')) ?></div>
                </td>
            </tr>
        </table>
        <div class="accent-bar"></div>
        <div class="accent-line"></div>
    </div>

    <!-- Repeating footer -->
    <div class="doc-footer">
        <table class="footer-table">
            <tr>
                <td style="width: 34%;">
                    <span class="footer-strong"><?= e($institution) ?></span><br>
                    Ref: <?= e($refNo) ?>
                </td>
                <td style="width: 32%;" class="footer-center">
                    Confidential &mdash; For internal use only<br>
                    Computer-generated, no signature required
                </td>
                <td style="width: 34%;" class="footer-right">
                    <span class="footer-strong pagenum"></span><br>
                    &copy; <?= date('Y') ?> <?= e($institution) ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- Document title block -->
    <div class="doc-title-block">
        <div class="doc-title-eyebrow">ATTENDANCE ANALYTICS REPORT</div>
        <div class="doc-title-main"><?= e($title) ?></div>
        <div class="doc-title-rule"></div>
        <table class="doc-title-meta">
            <tr>
                <td>
                    <div class="dt-label">Document Reference</div>
                    <div class="dt-value"><?= e($refNo) ?></div>
                </td>
                <td>
                    <div class="dt-label">Date of Issue</div>
                    <div class="dt-value"><?= e(Carbon::parse($date)->format('F d, Y')) ?></div>
                </td>
                <td>
                    <div class="dt-label">Prepared By</div>
                    <div class="dt-value"><?= e($generatedBy) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Report parameters -->
    <table class="meta-strip">
        <tr>
            <?php if (isset($semester) && $semester): ?>
            <td>
                <span class="meta-label">Semester</span>
                <span class="meta-value"><?= e($semester->name) ?></span>
            </td>
            <td>
                <span class="meta-label">Reporting Period</span>
                <span class="meta-value"><?= e(Carbon::parse($semester->start_date)->format('M d, Y')) ?> &ndash; <?= e(Carbon::parse($semester->end_date)->format('M d, Y')) ?></span>
            </td>
            <?php endif; ?>
            <td>
                <span class="meta-label">Total Records</span>
                <span class="meta-value"><?= e(number_format($total)) ?></span>
            </td>
            <td>
                <span class="meta-label">Unique Students</span>
                <span class="meta-value"><?= e(number_format($uniqueStudents)) ?></span>
            </td>
            <td>
                <span class="meta-label">Unique Events</span>
                <span class="meta-value"><?= e(number_format($uniqueEvents)) ?></span>
            </td>
        </tr>
    </table>

    <!-- Executive summary -->
    <div class="section">
        <table class="section-head">
            <tr>
                <td class="section-tick"></td>
                <td class="section-title">Executive Summary</td>
                <td class="section-hint">At a glance</td>
            </tr>
        </table>
        <table class="summary">
            <tr>
                <td>
                    <div class="card-label">Total Attendance Records</div>
                    <div class="card-value"><?= e(number_format($total)) ?></div>
                    <div class="card-foot">Check-ins captured in this period</div>
                </td>
                <td>
                    <div class="card-label">Unique Students</div>
                    <div class="card-value"><?= e(number_format($uniqueStudents)) ?></div>
                    <div class="card-foot">Distinct students recorded</div>
                </td>
                <td>
                    <div class="card-label">Unique Events</div>
                    <div class="card-value"><?= e(number_format($uniqueEvents)) ?></div>
                    <div class="card-foot">Events with attendance</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Attendance records -->
    <div class="section">
        <table class="section-head">
            <tr>
                <td class="section-tick"></td>
                <td class="section-title">Attendance Records</td>
                <td class="section-hint"><?= e(number_format($total)) ?> record<?= $total === 1 ? '' : 's' ?></td>
            </tr>
        </table>

        <?php if ($attendances->count() > 0): ?>
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 14%;">Student ID</th>
                    <th style="width: 22%;">Student Name</th>
                    <th style="width: 16%;">Department</th>
                    <th style="width: 21%;">Event</th>
                    <th style="width: 10%;">Method</th>
                    <th style="width: 12%;">Date &amp; Time</th>
                </tr>
            </thead>
            <tbody>
                <?php $index = 0; foreach ($attendances as $attendance): $index++; ?>
                <tr>
                    <td class="num"><?= $index ?></td>
                    <td class="strong"><?= e($attendance->student?->student_id) ?></td>
                    <td><?= e(trim(($attendance->student?->first_name ?? '') . ' ' . ($attendance->student?->last_name ?? ''))) ?: 'N/A' ?></td>
                    <td><?= e($attendance->student?->department?->name ?? 'N/A') ?></td>
                    <td><?= e(str_limit((string) $attendance->event?->name, 30)) ?></td>
                    <td>
                        <?php if ($attendance->method === 'qr'): ?>
                            <span class="badge badge-success">QR Code</span>
                        <?php elseif ($attendance->method === 'manual'): ?>
                            <span class="badge badge-warning">Manual</span>
                        <?php else: ?>
                            <span class="badge badge-info"><?= e(ucfirst((string) $attendance->method)) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= e(Carbon::parse($attendance->timestamp)->format('M d, Y h:i A')) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6">Total records</td>
                    <td class="text-right"><?= e(number_format($total)) ?></td>
                </tr>
            </tfoot>
        </table>
        <?php else: ?>
        <div class="no-data">No attendance records found for the selected criteria.</div>
        <?php endif; ?>
    </div>

    <?php if ($attendances->count() > 0): ?>
    <!-- Breakdown by department -->
    <div class="section">
        <table class="section-head">
            <tr>
                <td class="section-tick"></td>
                <td class="section-title">Distribution by Department</td>
                <td class="section-hint"><?= e($byDepartment->count()) ?> department<?= $byDepartment->count() === 1 ? '' : 's' ?></td>
            </tr>
        </table>
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 6%;">#</th>
                    <th style="width: 34%;">Department</th>
                    <th style="width: 34%;">Distribution</th>
                    <th style="width: 13%;" class="text-right">Records</th>
                    <th style="width: 13%;" class="text-right">Share</th>
                </tr>
            </thead>
            <tbody>
                <?php $row = 0; foreach ($byDepartment as $dept => $count): $row++; ?>
                <tr>
                    <td class="num"><?= $row ?></td>
                    <td class="strong"><?= e($dept) ?></td>
                    <td>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: <?= e(max(2, round(($count / $maxDept) * 100))) ?>%;"></div>
                        </div>
                    </td>
                    <td class="text-right"><?= e(number_format($count)) ?></td>
                    <td class="text-right strong"><?= e(number_format(($count / $total) * 100, 1)) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Total</td>
                    <td class="text-right"><?= e(number_format($total)) ?></td>
                    <td class="text-right">100.0%</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Breakdown by method -->
    <div class="section">
        <table class="section-head">
            <tr>
                <td class="section-tick"></td>
                <td class="section-title">Distribution by Check-in Method</td>
                <td class="section-hint"><?= e($byMethod->count()) ?> method<?= $byMethod->count() === 1 ? '' : 's' ?></td>
            </tr>
        </table>
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 6%;">#</th>
                    <th style="width: 34%;">Method</th>
                    <th style="width: 34%;">Distribution</th>
                    <th style="width: 13%;" class="text-right">Count</th>
                    <th style="width: 13%;" class="text-right">Share</th>
                </tr>
            </thead>
            <tbody>
                <?php $row = 0; foreach ($byMethod as $method => $count): $row++; ?>
                <tr>
                    <td class="num"><?= $row ?></td>
                    <td class="strong"><?= e(ucfirst((string) $method)) ?></td>
                    <td>
                        <div class="bar-track">
                            <div class="bar-fill-gold" style="width: <?= e(max(2, round(($count / $maxMethod) * 100))) ?>%;"></div>
                        </div>
                    </td>
                    <td class="text-right"><?= e(number_format($count)) ?></td>
                    <td class="text-right strong"><?= e(number_format(($count / $total) * 100, 1)) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Total</td>
                    <td class="text-right"><?= e(number_format($total)) ?></td>
                    <td class="text-right">100.0%</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>

    <!-- Certification -->
    <div class="section">
        <table class="section-head">
            <tr>
                <td class="section-tick"></td>
                <td class="section-title">Certification</td>
            </tr>
        </table>
        <div class="cert-note">
            This report was generated by the <strong><?= e($appName) ?></strong> platform on
            <strong><?= e(Carbon::parse($date)->format('F d, Y \a\t h:i A')) ?></strong>.
            The figures herein reflect attendance data recorded at the time of generation and are certified
            accurate for the stated reporting period. Reference <strong><?= e($refNo) ?></strong>.
        </div>
        <table class="sign-table">
            <tr>
                <td>
                    <div class="sign-blank"></div>
                    <div class="sign-line">
                        <div class="sign-role">Prepared By</div>
                        <div class="sign-name"><?= e($generatedBy) ?> &middot; Date: <?= e(Carbon::parse($date)->format('M d, Y')) ?></div>
                    </div>
                </td>
                <td>
                    <div class="sign-blank"></div>
                    <div class="sign-line">
                        <div class="sign-role">Approved By</div>
                        <div class="sign-name">Name &amp; Signature &middot; Date: _______________</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
