<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TailorScoreEvent extends Model
{
    protected $fillable = [
        'tailor_id',
        'order_id',
        'event',
        'delta',
        'score_after',
        'note',
    ];

    public function tailor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tailor_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
