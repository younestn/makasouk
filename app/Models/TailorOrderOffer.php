<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TailorOrderOffer extends Model
{
    use HasFactory;

    public const STATUS_UNREAD = 'unread';
    public const STATUS_READ = 'read';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_NOT_MY_SPECIALTY = 'not_my_specialty';
    public const STATUS_TAKEN = 'taken';

    public const REASON_NOT_MY_SPECIALTY = 'not_my_specialty';
    public const REASON_UNAVAILABLE = 'unavailable';
    public const REASON_WORKLOAD_FULL = 'workload_full';
    public const REASON_MEASUREMENTS_UNCLEAR = 'measurements_unclear';
    public const REASON_PRICING_NOT_SUITABLE = 'pricing_not_suitable';
    public const REASON_SHIPPING_TOO_FAR = 'shipping_too_far';
    public const REASON_OTHER = 'other';

    protected $fillable = [
        'order_id',
        'tailor_id',
        'status',
        'distance_km',
        'reason',
        'note',
        'read_at',
        'responded_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'float',
            'read_at' => 'datetime',
            'responded_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function tailor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tailor_id');
    }
}
