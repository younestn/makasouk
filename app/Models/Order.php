<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SEARCHING_FOR_TAILOR = 'searching_for_tailor';
    public const STATUS_NO_TAILORS_AVAILABLE = 'no_tailors_available';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY_FOR_DELIVERY = 'ready_for_delivery';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED_BY_CUSTOMER = 'cancelled_by_customer';
    public const STATUS_CANCELLED_BY_TAILOR = 'cancelled_by_tailor';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'customer_id',
        'tailor_id',
        'product_id',
        'measurements',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_location',
        'status',
        'cancellation_reason',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'measurements' => 'array',
            'delivery_latitude' => 'float',
            'delivery_longitude' => 'float',
            'accepted_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function tailor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tailor_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }
}
