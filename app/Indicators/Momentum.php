<?php
declare(strict_types=1);
namespace MarketForecast\Indicators;
final class Momentum implements IndicatorInterface { public function __construct(private readonly int $period) {} public function calculate(array $values): ?float { if(count($values)<$this->period+1)return null; return (float)$values[array_key_last($values)]-(float)$values[count($values)-1-$this->period]; } }
