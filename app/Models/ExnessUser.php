<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class ExnessUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exness_email',
        'exness_password_encrypted',
        'access_token',
        'token_expires_at',
        'last_sync_at',
        'api_response_v1',
        'api_response_v2',
        'is_active'
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'api_response_v1' => 'array',
        'api_response_v2' => 'array',
        'is_active' => 'boolean'
    ];

    protected $hidden = [
        'exness_password_encrypted',
        'access_token'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clients()
    {
        return $this->hasMany(ExnessClient::class);
    }

    // Accessors & Mutators
    public function setExnessPasswordAttribute($value)
    {
        $this->attributes['exness_password_encrypted'] = Crypt::encrypt($value);
    }

    public function getExnessPasswordAttribute()
    {
        return $this->attributes['exness_password_encrypted'] 
            ? Crypt::decrypt($this->attributes['exness_password_encrypted'])
            : null;
    }

    // Helper Methods
    public function isTokenValid()
    {
        return $this->access_token && 
               $this->token_expires_at && 
               $this->token_expires_at->isFuture();
    }

    public function isTokenExpired()
    {
        return !$this->isTokenValid();
    }

    public function needsSync($hours = 24)
    {
        return !$this->last_sync_at || 
               $this->last_sync_at->diffInHours(now()) >= $hours;
    }

    public function markSynced()
    {
        $this->update(['last_sync_at' => now()]);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithValidToken($query)
    {
        return $query->where('token_expires_at', '>', now())
                    ->whereNotNull('access_token');
    }

    public function scopeNeedsSync($query, $hours = 24)
    {
        return $query->where(function($q) use ($hours) {
            $q->whereNull('last_sync_at')
              ->orWhere('last_sync_at', '<', now()->subHours($hours));
        });
    }
}
