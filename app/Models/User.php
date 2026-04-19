<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
