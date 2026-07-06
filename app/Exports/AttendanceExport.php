<?php

namespace App\Exports;

use App\Core\Collection;

/**
 * Builds a CSV export of attendance records.
 * Replaces the maatwebsite/excel xlsx export with a dependency-free CSV.
 */
class AttendanceExport
{
    public function __construct(protected Collection $attendances)
    {
    }

    public function headings(): array
    {
        return [
            'ID',
            'Student ID',
            'Student Name',
            'Department',
            'Level',
            'Event Name',
            'Event Type',
            'Marked By',
            'Method',
            'Verified',
            'Timestamp',
        ];
    }

    public function map($attendance): array
    {
        $timestamp = $attendance->timestamp;
        return [
            $attendance->id,
            $attendance->student?->student_id,
            $attendance->student?->full_name,
            $attendance->student?->department?->name ?? '-',
            $attendance->student?->level,
            $attendance->event?->name,
            ucfirst(str_replace('_', ' ', (string) $attendance->event?->type)),
            $attendance->officer?->name,
            ucfirst((string) $attendance->method),
            $attendance->is_verified ? 'Yes' : 'No',
            $timestamp ? $timestamp->format('Y-m-d H:i:s') : '',
        ];
    }

    public function toCsv(): string
    {
        $handle = fopen('php://temp', 'r+');

        // UTF-8 BOM so Excel opens accented characters correctly.
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $this->headings());

        foreach ($this->attendances as $attendance) {
            fputcsv($handle, $this->map($attendance));
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
