<?php

declare(strict_types=1);

namespace App\Mcp;

use App\Garden\LateBlightRisk;
use App\Weather\OpenMeteoClient;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;

final readonly class DiseaseRiskTool
{
    public function __construct(
        private OpenMeteoClient $weather,
        private LateBlightRisk $lateBlightRisk,
    ) {
    }

    /**
     * Forecast late blight (mildiou) risk for tomatoes and potatoes in a city over the coming days.
     *
     * @return array<string, mixed>
     */
    #[McpTool(name: 'disease_risk', title: 'Late blight risk')]
    public function __invoke(
        #[Schema(description: 'City name, e.g. "Lyon"')]
        string $city,
        #[Schema(description: 'Number of forecast days', minimum: 2, maximum: 7)]
        int $days = 5,
    ): array {
        $location = $this->weather->geocode($city);
        if (null === $location) {
            return ['error' => \sprintf('City "%s" not found.', $city)];
        }

        $report = $this->lateBlightRisk->assess($this->weather->hourlyForecast($location, $days));
        $smithPeriods = array_column(array_filter($report, static fn (array $day): bool => $day['smith_period']), 'date');

        return [
            'location' => \sprintf('%s, %s', $location->name, $location->country),
            'disease' => 'late blight (Phytophthora infestans)',
            'crops' => ['tomato', 'potato'],
            'risk' => [] === $smithPeriods ? 'low' : 'high',
            'smith_period_dates' => array_values($smithPeriods),
            'days' => $report,
        ];
    }
}
