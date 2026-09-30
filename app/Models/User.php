<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    public function driverProfile(): HasOne
    {
        return $this->hasOne(DriverProfile::class, 'user_id');
    }

    public function deliveryOrdersAsOwner(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class, 'owner_id');
    }

    public function deliveryOrdersAsDriver(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class, 'driver_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    public function locationEvents(): HasMany
    {
        return $this->hasMany(LocationEvent::class, 'driver_id');
    }
}
