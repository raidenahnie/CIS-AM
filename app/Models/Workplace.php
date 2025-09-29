<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Workplace extends Model
{
    protected $fillable = [
        'name', 'address', 'latitude', 'longitude', 'radius', 'is_active', 'additional_settings'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'radius' => 'integer',
        'is_active' => 'boolean',
        'additional_settings' => 'json'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_workplaces')
                    ->withPivot(['role', 'is_primary'])
                    ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function distanceFrom($latitude, $longitude)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($latitude - $this->latitude);
        $dLng = deg2rad($longitude - $this->longitude);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) * sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    public function isWithinGeofence($latitude, $longitude)
    {
        return $this->distanceFrom($latitude, $longitude) <= $this->radius;
    }
}
