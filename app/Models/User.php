<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';

    protected array $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected array $hidden = [
        'password',
        'remember_token',
    ];

    public function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function eventOfficers()
    {
        return $this->hasMany(EventOfficer::class, 'user_id');
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_officers', 'user_id', 'event_id');
    }

    public function markedAttendances()
    {
        return $this->hasMany(Attendance::class, 'marked_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'performed_by');
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOfficer(): bool
    {
        return $this->role === 'officer';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function hasRole($roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles, true);
        }
        return $this->role === $roles;
    }
}
