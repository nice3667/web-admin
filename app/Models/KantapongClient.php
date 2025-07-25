<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class KantapongClient extends Model
{
    protected $table = 'kantapong_clients';

    protected $fillable = [
        'client_uid',
        'partner_account',
        'client_country',
        'reg_date',
        'volume_lots',
        'volume_mln_usd',
        'reward_usd',
        'rebate_amount_usd',
        'kyc_passed',
        'ftd_received',
        'ftt_made',
    ];

    protected $casts = [
        'reg_date' => 'datetime',
        'volume_lots' => 'decimal:4',
        'volume_mln_usd' => 'decimal:4',
        'reward_usd' => 'decimal:4',
        'rebate_amount_usd' => 'decimal:4',
        'kyc_passed' => 'boolean',
        'ftd_received' => 'boolean',
        'ftt_made' => 'boolean',
    ];

    /**
     * Scope for KYC passed clients
     */
    public function scopeKycPassed(Builder $query): Builder
    {
        return $query->where('kyc_passed', true);
    }

    /**
     * Scope for FTD received clients
     */
    public function scopeFtdReceived(Builder $query): Builder
    {
        return $query->where('ftd_received', true);
    }

    /**
     * Scope for FTT made clients
     */
    public function scopeFttMade(Builder $query): Builder
    {
        return $query->where('ftt_made', true);
    }

    /**
     * Scope for active clients (have volume or rewards)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('volume_lots', '>', 0)
              ->orWhere('reward_usd', '>', 0);
        });
    }

    /**
     * Get client status based on activity
     */
    public function getClientStatusAttribute(): string
    {
        if ($this->volume_lots > 0 || $this->reward_usd > 0) {
            return 'ACTIVE';
        }
        return 'INACTIVE';
    }
} 