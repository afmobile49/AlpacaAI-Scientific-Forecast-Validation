<?php
declare(strict_types=1);
namespace MarketForecast\AI;
interface AiProviderInterface { public function forecast(array $features, string $informationCutoff): array; }
