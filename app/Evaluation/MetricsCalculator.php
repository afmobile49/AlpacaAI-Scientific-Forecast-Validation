<?php
declare(strict_types=1);
namespace MarketForecast\Evaluation;
final class MetricsCalculator { public function directionCorrect(string $predicted,string $actual):bool{return $predicted===$actual;} public function absoluteError(float $predicted,float $actual):float{return abs($predicted-$actual);} public function squaredError(float $predicted,float $actual):float{$d=$predicted-$actual;return $d*$d;} public function brier(float $up,float $neutral,float $down,string $actual):float{$target=['BULLISH'=>[1,0,0],'NEUTRAL'=>[0,1,0],'BEARISH'=>[0,0,1]][$actual];return (($up-$target[0])**2)+(($neutral-$target[1])**2)+(($down-$target[2])**2);} public function rmse(array $errors):?float{return $errors?sqrt(array_sum($errors)/count($errors)):null;} }
