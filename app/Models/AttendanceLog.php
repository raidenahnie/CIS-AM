<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $fillable = [
        'user_id',
        'workplace_id', 
        'attendance_id',
        'action',
        'shift_type',
        'sequence',
        'timestamp',
        'latitude',
        'longitude', 
        'accuracy',
        'address',
        'is_valid_location',
        'distance_from_workplace',
        'method',
        'device_info',
        'ip_address',
        'user_agent',
        'notes',
        'metadata'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'is_valid_location' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'distance_from_workplace' => 'decimal:2',
        'metadata' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    // Scopes for querying
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('timestamp', $date);
    }

    public function scopeCheckIns($query)
    {
        return $query->where('action', 'check_in');
    }

    public function scopeCheckOuts($query)
    {
        return $query->where('action', 'check_out');
    }

    public function scopeBreaks($query)
    {
        return $query->whereIn('action', ['break_start', 'break_end']);
    }
}
