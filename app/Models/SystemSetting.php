<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        // Cast value based on type
        return match($setting->type) {
            'boolean' => (bool) $setting->value,
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Set a setting value by key
     */
    public static function set($key, $value)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return self::create([
                'key' => $key,
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : (is_array($value) ? 'json' : 'string'))
            ]);
        }
        
        // Cast value to string for storage (except json)
        $valueToStore = is_array($value) ? json_encode($value) : (is_bool($value) ? ($value ? '1' : '0') : $value);
        
        $setting->update(['value' => $valueToStore]);
        
        return $setting;
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAll()
    {
        $settings = self::all();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting->key] = match($setting->type) {
                'boolean' => (bool) $setting->value,
                'integer' => (int) $setting->value,
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        }
        
        return $result;
    }
}

