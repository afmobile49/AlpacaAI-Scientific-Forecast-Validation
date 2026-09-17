<?php
declare(strict_types=1);
namespace MarketForecast\Indicators;
final class SMA implements IndicatorInterface { public function __construct(private readonly int $period) {} public function calculate(array $values): ?float { if(count($values)<$this->period)return null; return array_sum(array_slice($values,-$this->period))/$this->period; } }
