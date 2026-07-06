<?php

namespace App\Models;

use App\Core\Model;

class EventOfficer extends Model
{
    protected string $table = 'event_officers';
    protected bool $timestamps = false;

    protected array $fillable = [
        'event_id',
        'user_id',
    ];

    // Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
