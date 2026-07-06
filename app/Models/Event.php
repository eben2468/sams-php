<?php

namespace App\Models;

use App\Core\Model;
use App\Core\QueryBuilder;
use Carbon\Carbon;

class Event extends Model
{
    protected string $table = 'events';

    protected array $fillable = [
        'name',
        'type',
        'start_time',
        'end_time',
        'description',
        'created_by',
        'semester_id',
    ];

    public function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time'   => 'datetime',
        ];
    }

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function eventOfficers()
    {
        return $this->hasMany(EventOfficer::class, 'event_id');
    }

    public function officers()
    {
        return $this->belongsToMany(User::class, 'event_officers', 'event_id', 'user_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Scopes
    public function scopeActive(QueryBuilder $query)
    {
        $now = Carbon::now()->toDateTimeString();
        return $query->where('start_time', '<=', $now)
                     ->where('end_time', '>=', $now);
    }

    public function scopeUpcoming(QueryBuilder $query)
    {
        return $query->where('start_time', '>', Carbon::now()->toDateTimeString())
                     ->orderBy('start_time', 'asc');
    }

    public function scopePast(QueryBuilder $query)
    {
        return $query->where('end_time', '<', Carbon::now()->toDateTimeString())
                     ->orderBy('start_time', 'desc');
    }

    public function scopeByType(QueryBuilder $query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeBySemester(QueryBuilder $query, $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    // Helper methods
    public function isActive(): bool
    {
        $now = Carbon::now();
        return $this->start_time <= $now && $this->end_time >= $now;
    }

    public function getAttendanceCount(): int
    {
        return $this->attendances()->count();
    }

    public function getAttendancePercentage(): float
    {
        $totalStudents = Student::active()->count();
        if ($totalStudents === 0) {
            return 0;
        }
        return ($this->getAttendanceCount() / $totalStudents) * 100;
    }
}
