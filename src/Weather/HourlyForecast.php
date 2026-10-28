<?php

declare(strict_types=1);

namespace App\Weather;

/**
 * One hour of forecast, as returned by Open-Meteo.
 */
final readonly class HourlyForecast
{
    public function __construct(
        public \DateTimeImmutable $time,
        public float $temperature,
        public float $relativeHumidity,
    ) {
    }
}
