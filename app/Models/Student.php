<?php

namespace App\Models;

use App\Core\Model;
use App\Core\QueryBuilder;

class Student extends Model
{
    protected string $table = 'students';

    protected array $fillable = [
        'student_id',
        'first_name',
        'last_name',
        'photo',
        'program_id',
        'level',
        'department_id',
        'faculty',
        'is_active',
    ];

    public function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'level'     => 'integer',
        ];
    }

    // Relationships
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Scopes
    public function scopeActive(QueryBuilder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDepartment(QueryBuilder $query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByLevel(QueryBuilder $query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeSearch(QueryBuilder $query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('student_id', 'like', "%{$search}%")
              ->orWhere('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%");
        });
    }
}
