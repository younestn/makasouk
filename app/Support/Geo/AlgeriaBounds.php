<?php

namespace App\Support\Geo;

class AlgeriaBounds
{
    public const SOUTH = 18.9;
    public const WEST = -8.7;
    public const NORTH = 37.2;
    public const EAST = 12.1;

    public static function contains(float $latitude, float $longitude): bool
    {
        return $latitude >= self::SOUTH
            && $latitude <= self::NORTH
            && $longitude >= self::WEST
            && $longitude <= self::EAST;
    }
}
