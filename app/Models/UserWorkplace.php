<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWorkplace extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workplace_id',
        'role',
        'is_primary',
        'assigned_at',
        'effective_from',
        'effective_until'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'assigned_at' => 'datetime',
        'effective_from' => 'datetime',
        'effective_until' => 'datetime'
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the workplace
     */
    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }
}space App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWorkplace extends Model
{
    //
}
