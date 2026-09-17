<?php
declare(strict_types=1);
namespace MarketForecast\Indicators;
final class Volatility implements IndicatorInterface { public function __construct(private readonly int $period) {} public function calculate(array $values): ?float { if(count($values)<$this->period+1)return null; $r=[];$s=count($values)-$this->period-1; for($i=$s+1;$i<count($values);$i++)$r[]=log($values[$i]/$values[$i-1]);$m=array_sum($r)/count($r);return sqrt(array_sum(array_map(fn($x)=>($x-$m)**2,$r))/count($r)); } }
