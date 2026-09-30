<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
        'group',
        'value',
        'type',
        'description',
    ];

    public static function getValue(string $key, $default = null)
    {
        $config = self::where('key', $key)->first();
        if (!$config) {
            return $default;
        }

        return match ($config->type) {
            'number' => is_numeric($config->value) ? (float) $config->value : $config->value,
            'boolean' => filter_var($config->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($config->value, true) ?? $default,
            default => $config->value,
        };
    }

    public static function setValue(string $key, $value): void
    {
        $config = self::firstOrNew(['key' => $key]);
        if (is_array($value) || is_object($value)) {
            $config->value = json_encode($value);
            $config->type = 'json';
        } elseif (is_bool($value)) {
            $config->value = $value ? '1' : '0';
            $config->type = 'boolean';
        } else {
            $config->value = (string) $value;
        }
        $config->save();
    }
}
