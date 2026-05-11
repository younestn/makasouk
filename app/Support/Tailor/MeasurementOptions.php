<?php

namespace App\Support\Tailor;

class MeasurementOptions
{
    public const UNIT_CM = 'cm';

    public const AUDIENCE_WOMEN = 'women';
    public const AUDIENCE_MEN = 'men';
    public const AUDIENCE_CHILDREN = 'children';
    public const AUDIENCE_UNISEX = 'unisex';

    /**
     * @var array<int, string>
     */
    public const AUDIENCES = [
        self::AUDIENCE_WOMEN,
        self::AUDIENCE_MEN,
        self::AUDIENCE_CHILDREN,
        self::AUDIENCE_UNISEX,
    ];

    /**
     * @return array<int, string>
     */
    public static function selectableAudiences(): array
    {
        return [
            self::AUDIENCE_WOMEN,
            self::AUDIENCE_MEN,
            self::AUDIENCE_CHILDREN,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function audienceOptions(): array
    {
        return collect(self::selectableAudiences())
            ->mapWithKeys(fn (string $audience): array => [$audience => self::audienceLabel($audience)])
            ->all();
    }

    public static function audienceLabel(?string $audience): string
    {
        return match ($audience) {
            self::AUDIENCE_WOMEN => __('admin.measurements.audiences.women'),
            self::AUDIENCE_MEN => __('admin.measurements.audiences.men'),
            self::AUDIENCE_CHILDREN => __('admin.measurements.audiences.children'),
            self::AUDIENCE_UNISEX => __('admin.measurements.audiences.unisex'),
            default => (string) str($audience ?? 'unknown')->headline(),
        };
    }

    /**
     * @param  array<int, string>|null  $audiences
     * @return array<int, string>
     */
    public static function normalizeAudiences(?array $audiences, ?string $legacyAudience = null): array
    {
        $normalized = collect($audiences ?? [])
            ->filter(fn ($audience): bool => is_string($audience) && in_array($audience, self::selectableAudiences(), true))
            ->unique()
            ->values()
            ->all();

        if ($normalized !== []) {
            return $normalized;
        }

        return match ($legacyAudience) {
            self::AUDIENCE_WOMEN => [self::AUDIENCE_WOMEN],
            self::AUDIENCE_MEN => [self::AUDIENCE_MEN],
            self::AUDIENCE_CHILDREN => [self::AUDIENCE_CHILDREN],
            default => self::selectableAudiences(),
        };
    }

    /**
     * @param  array<int, string>  $audiences
     */
    public static function legacyAudienceFromAudiences(array $audiences): string
    {
        $normalized = self::normalizeAudiences($audiences);

        if ($normalized === [] || count($normalized) === count(self::selectableAudiences())) {
            return self::AUDIENCE_UNISEX;
        }

        return $normalized[0];
    }

    /**
     * @param  array<int, string>|null  $audiences
     */
    public static function formatAudienceLabels(?array $audiences, string $separator = ', '): string
    {
        $normalized = self::normalizeAudiences($audiences);

        return implode($separator, array_map(
            fn (string $audience): string => self::audienceLabel($audience),
            $normalized,
        ));
    }
}
