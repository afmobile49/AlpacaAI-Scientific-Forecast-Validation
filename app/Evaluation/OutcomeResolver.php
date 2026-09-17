<?php
declare(strict_types=1);
namespace MarketForecast\Evaluation;
final class OutcomeResolver { public function direction(float $returnPct,float $threshold=0.5):string{if($threshold<0)throw new \InvalidArgumentException('Threshold cannot be negative.');return $returnPct>$threshold?'BULLISH':($returnPct<-$threshold?'BEARISH':'NEUTRAL');} public function returnPct(float $reference,float $actual):float{return $reference===0.0?throw new \InvalidArgumentException('Reference price cannot be zero.'):($actual-$reference)/$reference*100;} }
