<?php

declare(strict_types=1);

namespace App\Weather;

final readonly class Location
{
    public function __construct(
        public string $name,
        public string $country,
        public float $latitude,
        public float $longitude,
        public string $timezone,
    ) {
    }
}
