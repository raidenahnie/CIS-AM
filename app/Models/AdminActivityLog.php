<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    protected $fillable = [
        'admin_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'changes',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the admin user who performed the action
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Log an admin activity
     */
    public static function log($action, $description, $entityType = null, $entityId = null, $changes = null, $adminId = null)
    {
        // Use provided admin ID or get from auth
        $userId = $adminId ?? auth()->id();
        
        // Only log if we have a user ID
        if (!$userId) {
            return null;
        }
        
        return self::create([
            'admin_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}

