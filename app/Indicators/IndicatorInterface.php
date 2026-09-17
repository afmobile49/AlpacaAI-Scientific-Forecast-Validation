<?php
declare(strict_types=1);
namespace MarketForecast\Indicators;
interface IndicatorInterface { public function calculate(array $values): ?float; }
