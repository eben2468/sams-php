<?php

namespace App\Models;

use App\Core\Model;

class Department extends Model
{
    protected string $table = 'departments';

    protected array $fillable = ['name'];

    // Relationships
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    // Helper methods
    public function getStudentCount(): int
    {
        return $this->students()->count();
    }

    public function getActiveStudentCount(): int
    {
        return $this->students()->where('is_active', true)->count();
    }
}
