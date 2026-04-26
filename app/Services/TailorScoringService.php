<?php

namespace App\Services;

use App\Models\Order;
use App\Models\TailorProfile;
use App\Models\TailorScoreEvent;
use App\Models\User;

class TailorScoringService
{
    public const EVENT_ACCEPTED = 'accepted';
    public const EVENT_COMPLETED = 'completed';
    public const EVENT_REJECTED = 'rejected';
    public const EVENT_NOT_MY_SPECIALTY = 'not_my_specialty';
    public const EVENT_ACCEPTED_THEN_CANCELLED = 'accepted_then_cancelled';

    /**
     * @var array<string, int>
     */
    private const DELTAS = [
        self::EVENT_ACCEPTED => 0,
        self::EVENT_COMPLETED => 2,
        self::EVENT_REJECTED => -2,
        self::EVENT_NOT_MY_SPECIALTY => -1,
        self::EVENT_ACCEPTED_THEN_CANCELLED => -8,
    ];

    public function record(User|int $tailor, string $event, ?Order $order = null, ?string $note = null): void
    {
        $tailorId = $tailor instanceof User ? (int) $tailor->id : (int) $tailor;
        $delta = self::DELTAS[$event] ?? 0;

        $profile = TailorProfile::query()->firstOrCreate(
            ['user_id' => $tailorId],
            ['status' => TailorProfile::STATUS_OFFLINE, 'score' => 100],
        );

        $current = (int) ($profile->score ?? 100);
        $next = max(0, min(100, $current + $delta));

        if ($delta !== 0 || $event === self::EVENT_ACCEPTED) {
            $profile->forceFill(['score' => $next])->save();

            TailorScoreEvent::query()->create([
                'tailor_id' => $tailorId,
                'order_id' => $order?->id,
                'event' => $event,
                'delta' => $delta,
                'score_after' => $next,
                'note' => $note,
            ]);
        }
    }
}
