<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CustomOrderImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_order_id',
        'image_path',
        'sort_order',
    ];

    public function customOrder(): BelongsTo
    {
        return $this->belongsTo(CustomOrder::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return filled($this->image_path) ? Storage::disk('public')->url($this->image_path) : null;
    }
}
