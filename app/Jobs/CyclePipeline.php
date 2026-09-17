<?php
declare(strict_types=1);

namespace MarketForecast\Jobs;

final class CyclePipeline
{
    /** @param callable():array $forecast @param callable():array $report @param callable():array $email */
    public function run(callable $forecast, callable $report, callable $email): array
    {
        $forecastResult = $forecast();
        if (!in_array($forecastResult['status'] ?? '', ['FINALIZED', 'ALREADY_FINALIZED'], true)) {
            throw new \RuntimeException('Forecast cycle did not finalize.');
        }

        return [
            'forecast' => $forecastResult,
            'report' => $report(),
            'email' => $email(),
        ];
    }
}
