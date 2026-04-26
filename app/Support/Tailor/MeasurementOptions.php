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
     * @return array<string, string>
     */
    public static function audienceOptions(): array
    {
        return [
            self::AUDIENCE_WOMEN => 'Women',
            self::AUDIENCE_MEN => 'Men',
            self::AUDIENCE_CHILDREN => 'Children',
            self::AUDIENCE_UNISEX => 'Unisex',
        ];
    }
}
