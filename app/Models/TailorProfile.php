<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TailorProfile extends Model
{
    use HasFactory;

    public const STATUS_ONLINE = 'online';
    public const STATUS_OFFLINE = 'offline';

    protected $fillable = [
        'user_id',
        'category_id',
        'status',
        'average_rating',
        'total_reviews',
        'latitude',
        'longitude',
        'location',
    ];

    protected $attributes = [
        'status' => self::STATUS_OFFLINE,
        'average_rating' => 0,
        'total_reviews' => 0,
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'average_rating' => 'decimal:2',
            'total_reviews' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
