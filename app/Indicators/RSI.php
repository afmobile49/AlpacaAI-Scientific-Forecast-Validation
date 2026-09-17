<?php
declare(strict_types=1);
namespace MarketForecast\Indicators;
final class RSI implements IndicatorInterface { public function __construct(private readonly int $period=14) {} public function calculate(array $values): ?float { if(count($values)<$this->period+1)return null; $g=0.0;$l=0.0;$s=count($values)-$this->period-1; for($i=$s+1;$i<count($values);$i++){ $d=$values[$i]-$values[$i-1]; if($d>0)$g+=$d;else $l-=$d; } return $l===0.0?100.0:100-(100/(1+($g/$l))); } }
