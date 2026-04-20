<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_TAILOR = 'tailor';

    /**
     * @return array<int, string>
     */
    public static function roles(): array
    {
        return [self::ROLE_ADMIN, self::ROLE_CUSTOMER, self::ROLE_TAILOR];
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_suspended',
        'approved_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'is_suspended' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && $this->role === self::ROLE_ADMIN
            && ! $this->is_suspended;
    }

    public function tailorProfile(): HasOne
    {
        return $this->hasOne(TailorProfile::class);
    }

    public function customerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function acceptedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'tailor_id');
    }

    public function createdProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'created_by_admin_id');
    }

    public function writtenReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    public function receivedReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'tailor_id');
    }
}