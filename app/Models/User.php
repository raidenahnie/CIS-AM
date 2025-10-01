<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'last_activity',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_activity' => 'datetime',
        ];
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a regular user.
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Scope a query to only include admin users.
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope a query to only include regular users.
     */
    public function scopeUsers($query)
    {
        return $query->where('role', 'user');
    }

    /**
     * Get the workplaces assigned to this user.
     */
    public function workplaces()
    {
        return $this->belongsToMany(Workplace::class, 'user_workplaces')
                    ->withPivot(['role', 'is_primary', 'assigned_at', 'effective_from', 'effective_until'])
                    ->withTimestamps();
    }

    /**
     * Get the primary workplace for this user.
     */
    public function primaryWorkplace()
    {
        return $this->workplaces()->wherePivot('is_primary', true)->first();
    }

    /**
     * Get all active workplaces for this user.
     */
    public function activeWorkplaces()
    {
        return $this->workplaces()
                    ->wherePivot('effective_from', '<=', now())
                    ->where(function ($query) {
                        $query->wherePivotNull('effective_until')
                              ->orWherePivot('effective_until', '>', now());
                    });
    }

    /**
     * Check if user is online (activity within last 5 minutes)
     */
    public function isOnline(): bool
    {
        return $this->last_activity && $this->last_activity->gt(now()->subMinutes(5));
    }

    /**
     * Update user's last activity
     */
    public function updateLastActivity(): void
    {
        $this->last_activity = now();
        $this->save();
    }

    /**
     * Scope to get only online users
     */
    public function scopeOnline($query)
    {
        return $query->where('last_activity', '>=', now()->subMinutes(5));
    }
}
