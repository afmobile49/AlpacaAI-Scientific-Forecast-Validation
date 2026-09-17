<?php
declare(strict_types=1);
namespace MarketForecast\Indicators;
final class EMA implements IndicatorInterface { public function __construct(private readonly int $period) {} public function calculate(array $values): ?float { if(count($values)<$this->period)return null; $v=array_values($values); $e=array_sum(array_slice($v,0,$this->period))/$this->period; $k=2/($this->period+1); for($i=$this->period;$i<count($v);$i++)$e=((float)$v[$i]-$e)*$k+$e; return $e; } }
