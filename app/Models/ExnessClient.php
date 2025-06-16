<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ExnessClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'exness_user_id',
        'client_uid',
        'client_name',
        'client_email',
        'client_status',
        'volume_lots',
        'volume_mln_usd',
        'reward_usd',
        'rebate_amount_usd',
        'currency',
        'reg_date',
        'last_activity',
        'raw_data_v1',
        'raw_data_v2',
        'synced_at'
    ];

    protected $casts = [
        'volume_lots' => 'decimal:4',
        'volume_mln_usd' => 'decimal:4', 
        'reward_usd' => 'decimal:2',
        'rebate_amount_usd' => 'decimal:2',
        'reg_date' => 'date',
        'last_activity' => 'datetime',
        'synced_at' => 'datetime',
        'raw_data_v1' => 'array',
        'raw_data_v2' => 'array'
    ];

    // Relationships
    public function exnessUser()
    {
        return $this->belongsTo(ExnessUser::class);
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, ExnessUser::class, 'id', 'id', 'exness_user_id', 'user_id');
    }

    // Accessors
    public function getStatusColorAttribute()
    {
        return match(strtoupper($this->client_status)) {
            'ACTIVE' => 'text-green-600',
            'INACTIVE' => 'text-red-600',
            default => 'text-gray-600'
        };
    }

    public function getFormattedVolumeLotsAttribute()
    {
        return number_format($this->volume_lots, 2);
    }
    
    public function getFormattedVolumeUsdAttribute()
    {
        return number_format($this->volume_mln_usd, 4);
    }

    public function getFormattedRewardAttribute()
    {
        return number_format($this->reward_usd, 2);
    }

    public function getFormattedRebateAttribute()
    {
        return $this->rebate_amount_usd !== null ? number_format($this->rebate_amount_usd, 2) : '-';
    }

    // Helper Methods
    public function isActive()
    {
        return strtoupper($this->client_status) === 'ACTIVE';
    }

    public function isInactive()
    {
        return strtoupper($this->client_status) === 'INACTIVE';
    }

    public function wasRecentlyActive($days = 30)
    {
        return $this->last_activity && 
               $this->last_activity->diffInDays(now()) <= $days;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('client_status', 'ACTIVE');
    }

    public function scopeInactive($query)
    {
        return $query->where('client_status', 'INACTIVE');
    }

    public function scopeByStatus($query, $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('client_status', strtoupper($status));
        }
        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('client_uid', 'like', "%{$search}%")
                        ->orWhere('client_name', 'like', "%{$search}%")
                        ->orWhere('client_email', 'like', "%{$search}%");
        }
        return $query;
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->where('reg_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('reg_date', '<=', $endDate);
        }
        return $query;
    }

    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('exnessUser', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopeRecentlyUpdated($query, $hours = 24)
    {
        return $query->where('synced_at', '>=', now()->subHours($hours));
    }

    // Statistics Methods
    public static function getStatsForUser($userId)
    {
        $clients = self::forUser($userId)->get();
        
        return [
            'total_accounts' => $clients->count(),
            'active_accounts' => $clients->where('client_status', 'ACTIVE')->count(),
            'inactive_accounts' => $clients->where('client_status', 'INACTIVE')->count(),
            'total_volume_lots' => $clients->sum('volume_lots'),
            'total_volume_usd' => $clients->sum('volume_mln_usd'),
            'total_reward' => $clients->sum('reward_usd'),
            'total_rebate' => $clients->sum('rebate_amount_usd')
        ];
    }
}
