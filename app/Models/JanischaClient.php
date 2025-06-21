<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class JanischaClient extends Model
{
    use HasFactory;

    protected $table = 'janischa_clients';

    protected $fillable = [
        'partner_account',
        'client_uid',
        'client_id',
        'reg_date',
        'client_country',
        'volume_lots',
        'volume_mln_usd',
        'reward_usd',
        'client_status',
        'kyc_passed',
        'ftd_received',
        'ftt_made',
        'raw_data',
        'last_sync_at',
    ];

    protected $casts = [
        'reg_date' => 'date',
        'volume_lots' => 'decimal:8',
        'volume_mln_usd' => 'decimal:8',
        'reward_usd' => 'decimal:8',
        'kyc_passed' => 'boolean',
        'ftd_received' => 'boolean',
        'ftt_made' => 'boolean',
        'raw_data' => 'array',
        'last_sync_at' => 'datetime',
    ];

    protected $dates = [
        'reg_date',
        'last_sync_at',
    ];

    // Scope for active clients
    public function scopeActive($query)
    {
        return $query->where('client_status', '!=', 'INACTIVE');
    }

    // Scope for clients with KYC passed
    public function scopeKycPassed($query)
    {
        return $query->where('kyc_passed', true);
    }

    // Scope for clients with FTD received
    public function scopeFtdReceived($query)
    {
        return $query->where('ftd_received', true);
    }

    // Scope for clients by country
    public function scopeByCountry($query, $country)
    {
        return $query->where('client_country', $country);
    }

    // Accessor for formatted volume
    protected function formattedVolumeLots(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => number_format($this->volume_lots, 2),
        );
    }

    // Accessor for formatted reward
    protected function formattedRewardUsd(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => number_format($this->reward_usd, 2),
        );
    }
} 