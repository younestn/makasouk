<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CustomOrder extends Model
{
    use HasFactory;

    public const STATUS_PLACED = 'placed';
    public const STATUS_ADMIN_REVIEW = 'admin_review';
    public const STATUS_QUOTED = 'quoted';
    public const STATUS_QUOTE_ACCEPTED = 'quote_accepted';
    public const STATUS_QUOTE_REJECTED = 'quote_rejected';
    public const STATUS_TAILOR_ASSIGNMENT_PENDING = 'tailor_assignment_pending';
    public const STATUS_ASSIGNED_TO_TAILOR = 'assigned_to_tailor';
    public const STATUS_WORK_STARTED = 'work_started';
    public const STATUS_CUTTING_STARTED = 'cutting_started';
    public const STATUS_SEWING_STARTED = 'sewing_started';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_SENT_TO_SHIPPING_CENTER = 'sent_to_shipping_center';
    public const STATUS_ARRIVED = 'arrived';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'customer_id',
        'tailor_id',
        'title',
        'tailor_specialty',
        'fabric_type',
        'measurements',
        'notes',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_work_wilaya',
        'status',
        'quote_amount',
        'quote_note',
        'quoted_at',
        'quote_rejection_note',
        'accepted_at',
        'assigned_at',
        'assignment_meta',
    ];

    protected function casts(): array
    {
        return [
            'measurements' => 'array',
            'delivery_latitude' => 'float',
            'delivery_longitude' => 'float',
            'quote_amount' => 'decimal:2',
            'quoted_at' => 'datetime',
            'accepted_at' => 'datetime',
            'assigned_at' => 'datetime',
            'assignment_meta' => 'array',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function allStatuses(): array
    {
        return [
            self::STATUS_PLACED,
            self::STATUS_ADMIN_REVIEW,
            self::STATUS_QUOTED,
            self::STATUS_QUOTE_ACCEPTED,
            self::STATUS_QUOTE_REJECTED,
            self::STATUS_TAILOR_ASSIGNMENT_PENDING,
            self::STATUS_ASSIGNED_TO_TAILOR,
            self::STATUS_WORK_STARTED,
            self::STATUS_CUTTING_STARTED,
            self::STATUS_SEWING_STARTED,
            self::STATUS_COMPLETED,
            self::STATUS_PREPARING,
            self::STATUS_SENT_TO_SHIPPING_CENTER,
            self::STATUS_ARRIVED,
            self::STATUS_RECEIVED,
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
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

    public function images(): HasMany
    {
        return $this->hasMany(CustomOrderImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function trackingEvents(): MorphMany
    {
        return $this->morphMany(TrackingEvent::class, 'trackable')->orderBy('occurred_at')->orderBy('id');
    }
}
