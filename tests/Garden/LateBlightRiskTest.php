<?php

declare(strict_types=1);

namespace App\Tests\Garden;

use App\Garden\LateBlightRisk;
use App\Weather\HourlyForecast;
use PHPUnit\Framework\TestCase;

final class LateBlightRiskTest extends TestCase
{
    public function testTwoConsecutiveWarmHumidDaysMakeASmithPeriod(): void
    {
        $forecast = [...$this->day('2026-06-01', 14.0, 12), ...$this->day('2026-06-02', 15.0, 11)];

        $report = (new LateBlightRisk())->assess($forecast);

        self::assertFalse($report[0]['smith_period'], 'The first favourable day alone is not a Smith Period.');
        self::assertTrue($report[1]['smith_period']);
    }

    public function testColdNightBreaksTheSmithPeriod(): void
    {
        $forecast = [...$this->day('2026-06-01', 14.0, 12), ...$this->day('2026-06-02', 9.5, 20)];

        $report = (new LateBlightRisk())->assess($forecast);

        self::assertFalse($report[1]['favourable']);
        self::assertFalse($report[1]['smith_period']);
    }

    public function testTenHumidHoursIsNotEnough(): void
    {
        $forecast = [...$this->day('2026-06-01', 14.0, 10), ...$this->day('2026-06-02', 14.0, 10)];

        $report = (new LateBlightRisk())->assess($forecast);

        self::assertSame(10, $report[1]['humid_hours']);
        self::assertFalse($report[1]['smith_period']);
    }

    /**
     * 24 hours at or above $minTemperature, the first $humidHours of them at 95 % humidity.
     *
     * @return list<HourlyForecast>
     */
    private function day(string $date, float $minTemperature, int $humidHours): array
    {
        $hours = [];
        for ($h = 0; $h < 24; ++$h) {
            $hours[] = new HourlyForecast(
                time: new \DateTimeImmutable(\sprintf('%s %02d:00', $date, $h)),
                temperature: $minTemperature + ($h % 12),
                relativeHumidity: $h < $humidHours ? 95.0 : 60.0,
            );
        }

        return $hours;
    }
}
