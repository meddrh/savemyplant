<?php

declare(strict_types=1);

namespace App\Tests\Mcp;

use App\Garden\LateBlightRisk;
use App\Mcp\DiseaseRiskTool;
use App\Weather\OpenMeteoClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

final class DiseaseRiskToolTest extends TestCase
{
    public function testReportsHighRiskForAHumidForecast(): void
    {
        $time = $temperature = $humidity = [];
        foreach (['2026-06-01', '2026-06-02'] as $date) {
            for ($h = 0; $h < 24; ++$h) {
                $time[] = \sprintf('%sT%02d:00', $date, $h);
                $temperature[] = 16.0;
                $humidity[] = $h < 12 ? 96 : 70;
            }
        }

        $tool = $this->tool([
            new JsonMockResponse(['results' => [[
                'name' => 'Lyon', 'country' => 'France', 'latitude' => 45.75, 'longitude' => 4.85, 'timezone' => 'Europe/Paris',
            ]]]),
            new JsonMockResponse(['hourly' => [
                'time' => $time, 'temperature_2m' => $temperature, 'relative_humidity_2m' => $humidity,
            ]]),
        ]);

        $result = $tool('Lyon', 2);

        self::assertSame('Lyon, France', $result['location']);
        self::assertSame('high', $result['risk']);
        self::assertSame(['2026-06-02'], $result['smith_period_dates']);
    }

    public function testUnknownCity(): void
    {
        $tool = $this->tool([new JsonMockResponse(['generationtime_ms' => 0.1])]);

        self::assertSame(['error' => 'City "Atlantis" not found.'], $tool('Atlantis'));
    }

    /**
     * @param list<JsonMockResponse> $responses
     */
    private function tool(array $responses): DiseaseRiskTool
    {
        return new DiseaseRiskTool(new OpenMeteoClient(new MockHttpClient($responses)), new LateBlightRisk());
    }
}
