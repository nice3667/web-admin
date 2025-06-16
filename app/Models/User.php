<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'exness_email',
        'exness_password_encrypted',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'exness_password_encrypted',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->setUsername();
        });
    }

    protected function usernameExists(string $username): bool
    {
        return self::where('username', $username)->exists();
    }

    public function setUsername(): void
    {
        // Early return if username is already set
        if ($this->username) {
            return;
        }

        $baseUsername = $this->generateBaseUsername();
        $this->username = $this->generateUniqueUsername($baseUsername);
    }

    private function generateBaseUsername(): string
    {
        return Str::of($this->name)
            ->ascii()
            ->lower()
            ->replaceMatches('/[\s._-]+/', '') // Replace multiple special characters at once
            ->trim();
    }

    private function generateUniqueUsername(string $baseUsername): string
    {
        $username = $baseUsername;

        // If base username is already unique, return it
        if (! $this->usernameExists($username)) {
            return $username;
        }

        // Generate a random suffix between 100000 and 999999
        $suffix = random_int(100000, 999999);
        $username = $baseUsername.$suffix;

        // In the unlikely case of collision, increment until unique
        while ($this->usernameExists($username)) {
            $suffix++;
            $username = $baseUsername.$suffix;
        }

        return $username;
    }
}
