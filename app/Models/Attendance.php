<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workplace_id',
        'is_assigned_workplace',
        'date',
        'check_in_time',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_accuracy',
        'check_in_address',
        'check_in_method',
        'check_out_time',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_accuracy',
        'check_out_address',
        'check_out_method',
        'total_hours',
        'break_duration',
        'distance_from_workplace',
        'status',
        'is_valid_location',
        'requires_approval',
        'is_approved',
        'approved_by',
        'approved_at',
        'notes',
        'metadata'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'check_in_latitude' => 'decimal:8',
        'check_in_longitude' => 'decimal:8',
        'check_out_latitude' => 'decimal:8',
        'check_out_longitude' => 'decimal:8',
        'distance_from_workplace' => 'decimal:2',
        'is_valid_location' => 'boolean',
        'is_assigned_workplace' => 'boolean',
        'requires_approval' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'metadata' => 'json'
    ];

    /**
     * Get the user that owns the attendance
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the workplace for this attendance
     */
    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }

    /**
     * Get the approver of this attendance
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get all logs for this attendance
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    /**
     * Calculate total work hours in minutes
     */
    public function calculateTotalHours()
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return 0;
        }

        $checkIn = $this->check_in_time;
        $checkOut = $this->check_out_time;
        
        $totalMinutes = $checkOut->diffInMinutes($checkIn);
        $this->total_hours = $totalMinutes - ($this->break_duration ?? 0);
        
        return $this->total_hours;
    }

    /**
     * Check if user is late
     */
    public function isLate($expectedStartTime = '08:00')
    {
        if (!$this->check_in_time) {
            return false;
        }

        $expected = \Carbon\Carbon::createFromTimeString($expectedStartTime);
        return $this->check_in_time->format('H:i') > $expected->format('H:i');
    }

    /**
     * Get formatted total hours
     */
    public function getFormattedTotalHoursAttribute()
    {
        if (!$this->total_hours) {
            return '0h 0m';
        }

        $hours = intval($this->total_hours / 60);
        $minutes = $this->total_hours % 60;
        
        return "{$hours}h {$minutes}m";
    }
}
