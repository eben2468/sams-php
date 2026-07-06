<?php

namespace App\Models;

use App\Core\Model;
use App\Core\QueryBuilder;
use Carbon\Carbon;

class Semester extends Model
{
    protected string $table = 'semesters';

    protected array $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'description',
    ];

    public function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'is_active'  => 'boolean',
        ];
    }

    // Relationships
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    // Scopes
    public function scopeActive(QueryBuilder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent(QueryBuilder $query)
    {
        $now = Carbon::now()->toDateString();
        return $query->where('start_date', '<=', $now)
                     ->where('end_date', '>=', $now);
    }

    public function scopeUpcoming(QueryBuilder $query)
    {
        return $query->where('start_date', '>', Carbon::now()->toDateString())
                     ->orderBy('start_date', 'asc');
    }

    public function scopePast(QueryBuilder $query)
    {
        return $query->where('end_date', '<', Carbon::now()->toDateString())
                     ->orderBy('end_date', 'desc');
    }

    // Helper methods
    public function isCurrent(): bool
    {
        $now = Carbon::now();
        return $this->start_date <= $now && $this->end_date >= $now;
    }

    public function getEventCount(): int
    {
        return $this->events()->count();
    }

    public function getTotalAttendance(): int
    {
        return (int) \App\Core\Database::scalar(
            'SELECT COUNT(*) FROM attendance a JOIN events e ON a.event_id = e.id WHERE e.semester_id = ?',
            [$this->id]
        );
    }

    // Static helpers
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }

    public static function setActive($semesterId): void
    {
        static::query()->update(['is_active' => false]);
        static::where('id', $semesterId)->update(['is_active' => true]);
    }
}
