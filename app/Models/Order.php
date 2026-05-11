<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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



    /**
     * @return array<int, string>
     */
    public static function allStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_SEARCHING_FOR_TAILOR,
            self::STATUS_NO_TAILORS_AVAILABLE,
            self::STATUS_ACCEPTED,
            self::STATUS_PROCESSING,
            self::STATUS_READY_FOR_DELIVERY,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED_BY_CUSTOMER,
            self::STATUS_CANCELLED_BY_TAILOR,
            self::STATUS_CANCELLED,
        ];
    }

    protected $fillable = [
        'customer_id',
        'tailor_id',
        'product_id',
        'measurements',
        'order_configuration',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_work_wilaya',
        'delivery_commune',
        'delivery_neighborhood',
        'delivery_location_label',
        'shipping_company_id',
        'shipping_company_name',
        'delivery_type',
        'delivery_phone',
        'delivery_email',
        'status',
        'tracking_stage',
        'matched_specialization',
        'matching_snapshot',
        'subtotal_amount',
        'shipping_amount',
        'platform_commission_amount',
        'tailor_net_amount',
        'cancellation_reason',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'measurements' => 'array',
            'order_configuration' => 'array',
            'delivery_latitude' => 'float',
            'delivery_longitude' => 'float',
            'matching_snapshot' => 'array',
            'tracking_stage' => 'string',
            'subtotal_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'platform_commission_amount' => 'decimal:2',
            'tailor_net_amount' => 'decimal:2',
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

    public function shippingCompany(): BelongsTo
    {
        return $this->belongsTo(ShippingCompany::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function tailorOffers(): HasMany
    {
        return $this->hasMany(TailorOrderOffer::class);
    }

    public function trackingEvents(): MorphMany
    {
        return $this->morphMany(TrackingEvent::class, 'trackable')->orderBy('occurred_at')->orderBy('id');
    }
}
