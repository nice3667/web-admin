<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_account',
        'client_uid',
        'client_id',
        'reg_date',
        'client_country',
        'volume_lots',
        'volume_mln_usd',
        'reward_usd',
        'rebate_amount_usd',
        'client_status',
        'kyc_passed',
        'ftd_received',
        'ftt_made',
        'raw_data',
        'last_sync_at',
        'user_id'
    ];

    protected $casts = [
        'reg_date' => 'date',
        'volume_lots' => 'decimal:8',
        'volume_mln_usd' => 'decimal:8',
        'reward_usd' => 'decimal:8',
        'rebate_amount_usd' => 'decimal:8',
        'kyc_passed' => 'boolean',
        'ftd_received' => 'boolean',
        'ftt_made' => 'boolean',
        'raw_data' => 'array',
        'last_sync_at' => 'datetime'
    ];

    /**
     * Get the user that owns the client.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('client_status', 'ACTIVE');
    }

    public function scopeInactive($query)
    {
        return $query->where('client_status', 'INACTIVE');
    }

    public function scopeKycPassed($query)
    {
        return $query->where('kyc_passed', true);
    }

    public function scopeFtdReceived($query)
    {
        return $query->where('ftd_received', true);
    }

    public function scopeFttMade($query)
    {
        return $query->where('ftt_made', true);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('client_country', $country);
    }

    public function scopeByPartnerAccount($query, $partnerAccount)
    {
        return $query->where('partner_account', $partnerAccount);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('reg_date', [$startDate, $endDate]);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
