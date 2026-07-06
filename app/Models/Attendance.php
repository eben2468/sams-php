<?php

namespace App\Models;

use App\Core\Model;
use App\Core\QueryBuilder;

class Attendance extends Model
{
    protected string $table = 'attendance';
    protected bool $timestamps = false;

    protected array $fillable = [
        'event_id',
        'student_id',
        'marked_by',
        'method',
        'is_verified',
        'timestamp',
    ];

    public function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'timestamp'   => 'datetime',
        ];
    }

    // Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function officer()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    // Scopes
    public function scopeByEvent(QueryBuilder $query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeByStudent(QueryBuilder $query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByMethod(QueryBuilder $query, $method)
    {
        return $query->where('method', $method);
    }

    public function scopeVerified(QueryBuilder $query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeUnverified(QueryBuilder $query)
    {
        return $query->where('is_verified', false);
    }
}
