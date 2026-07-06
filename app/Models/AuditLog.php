<?php

namespace App\Models;

use App\Core\Model;
use App\Core\QueryBuilder;
use Carbon\Carbon;

class AuditLog extends Model
{
    protected string $table = 'audit_logs';
    protected bool $timestamps = false;

    protected array $fillable = [
        'action',
        'performed_by',
        'target_type',
        'target_id',
        'metadata',
        'timestamp',
    ];

    public function casts(): array
    {
        return [
            'metadata'  => 'array',
            'timestamp' => 'datetime',
        ];
    }

    /**
     * JSON-encode array metadata on the way into the database.
     */
    public static function create(array $attributes): static
    {
        if (isset($attributes['metadata']) && is_array($attributes['metadata'])) {
            $attributes['metadata'] = json_encode($attributes['metadata']);
        }
        if (!isset($attributes['timestamp'])) {
            $attributes['timestamp'] = date('Y-m-d H:i:s');
        }
        return parent::create($attributes);
    }

    // Relationships
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Scopes
    public function scopeByUser(QueryBuilder $query, $userId)
    {
        return $query->where('performed_by', $userId);
    }

    public function scopeByAction(QueryBuilder $query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByTargetType(QueryBuilder $query, $targetType)
    {
        return $query->where('target_type', $targetType);
    }

    public function scopeRecent(QueryBuilder $query, $days = 30)
    {
        return $query->where('timestamp', '>=', Carbon::now()->subDays($days)->toDateTimeString());
    }
}
