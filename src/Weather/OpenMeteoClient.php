<?php

declare(strict_types=1);

namespace App\Weather;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Open-Meteo is free and needs no API key, so the demo runs right after git clone.
 *
 * @see https://open-meteo.com/en/docs
 */
final readonly class OpenMeteoClient
{
    private const GEOCODING_URL = 'https://geocoding-api.open-meteo.com/v1/search';
    private const FORECAST_URL = 'https://api.open-meteo.com/v1/forecast';

    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    public function geocode(string $city): ?Location
    {
        $data = $this->httpClient->request('GET', self::GEOCODING_URL, [
            'query' => ['name' => $city, 'count' => 1, 'format' => 'json'],
        ])->toArray();

        $result = $data['results'][0] ?? null;
        if (null === $result) {
            return null;
        }

        return new Location(
            name: $result['name'],
            country: $result['country'] ?? '',
            latitude: (float) $result['latitude'],
            longitude: (float) $result['longitude'],
            timezone: $result['timezone'] ?? 'UTC',
        );
    }

    /**
     * @return list<HourlyForecast>
     */
    public function hourlyForecast(Location $location, int $days): array
    {
        $data = $this->httpClient->request('GET', self::FORECAST_URL, [
            'query' => [
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'hourly' => 'temperature_2m,relative_humidity_2m',
                'forecast_days' => $days,
                'timezone' => $location->timezone,
            ],
        ])->toArray();

        $timezone = new \DateTimeZone($location->timezone);
        $hourly = $data['hourly'];
        $forecast = [];
        foreach ($hourly['time'] as $i => $time) {
            $temperature = $hourly['temperature_2m'][$i];
            $humidity = $hourly['relative_humidity_2m'][$i];
            if (null === $temperature || null === $humidity) {
                continue;
            }

            $forecast[] = new HourlyForecast(
                time: new \DateTimeImmutable($time, $timezone),
                temperature: (float) $temperature,
                relativeHumidity: (float) $humidity,
            );
        }

        return $forecast;
    }
}
