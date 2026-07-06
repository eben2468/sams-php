<?php

namespace App\Models;

use App\Core\Model;

class Program extends Model
{
    protected string $table = 'programs';

    protected array $fillable = [
        'name',
        'department_id',
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // Helper methods
    public function getStudentCount(): int
    {
        return $this->students()->count();
    }
}
