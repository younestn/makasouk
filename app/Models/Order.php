<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'tailor_id',
        'product_id',
        'measurements',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_location',
        'status',
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
}
