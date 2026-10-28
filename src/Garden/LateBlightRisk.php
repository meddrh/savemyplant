<?php

declare(strict_types=1);

namespace App\Garden;

use App\Weather\HourlyForecast;

/**
 * Late blight (Phytophthora infestans) risk for tomatoes and potatoes, using the
 * Smith Period: two consecutive days where the minimum temperature stays at or
 * above 10 °C and relative humidity reaches 90 % for at least 11 hours.
 */
final class LateBlightRisk
{
    private const MIN_TEMPERATURE = 10.0;
    private const HUMIDITY_THRESHOLD = 90.0;
    private const MIN_HUMID_HOURS = 11;

    /**
     * @param list<HourlyForecast> $forecast
     *
     * @return list<array{date: string, min_temperature: float, humid_hours: int, favourable: bool, smith_period: bool}>
     */
    public function assess(array $forecast): array
    {
        $days = [];
        foreach ($forecast as $hour) {
            $date = $hour->time->format('Y-m-d');
            $days[$date] ??= ['min_temperature' => \INF, 'humid_hours' => 0];
            $days[$date]['min_temperature'] = min($days[$date]['min_temperature'], $hour->temperature);
            if ($hour->relativeHumidity >= self::HUMIDITY_THRESHOLD) {
                ++$days[$date]['humid_hours'];
            }
        }

        $report = [];
        $previousFavourable = false;
        foreach ($days as $date => $day) {
            $favourable = $day['min_temperature'] >= self::MIN_TEMPERATURE
                && $day['humid_hours'] >= self::MIN_HUMID_HOURS;

            $report[] = [
                'date' => $date,
                'min_temperature' => $day['min_temperature'],
                'humid_hours' => $day['humid_hours'],
                'favourable' => $favourable,
                'smith_period' => $favourable && $previousFavourable,
            ];
            $previousFavourable = $favourable;
        }

        return $report;
    }
}
