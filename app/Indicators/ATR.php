<?php
declare(strict_types=1);
namespace MarketForecast\Indicators;
final class ATR implements IndicatorInterface { public function __construct(private readonly int $period=14) {} public function calculate(array $values): ?float { return null; } public function calculateFromBars(array $bars): ?float { if(count($bars)<$this->period+1)return null;$t=[];for($i=count($bars)-$this->period;$i<count($bars);$i++){$b=$bars[$i];$p=$bars[$i-1]['close'];$t[]=max($b['high']-$b['low'],abs($b['high']-$p),abs($b['low']-$p));}return array_sum($t)/$this->period; } }
