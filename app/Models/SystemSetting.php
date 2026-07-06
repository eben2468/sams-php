<?php

namespace App\Models;

use App\Core\Model;

class SystemSetting extends Model
{
    protected string $table = 'system_settings';
    protected bool $timestamps = false;
    protected bool $incrementing = false;
    protected string $primaryKey = 'key';

    protected array $fillable = [
        'key',
        'value',
    ];

    // Helper methods
    public static function get(string $key, $default = null)
    {
        $setting = static::find($key);
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
