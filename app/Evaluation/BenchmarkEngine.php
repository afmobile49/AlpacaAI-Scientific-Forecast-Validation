<?php
declare(strict_types=1);
namespace MarketForecast\Evaluation;
final class BenchmarkEngine
{
    public function predict(string $code,array $f): string { return match($code){'ALWAYS_BULLISH'=>'BULLISH','PREVIOUS_SESSION_DIRECTION'=>(string)($f['previous_session_direction']??'NEUTRAL'),'MOMENTUM_5'=>$this->sign($f['momentum5']??0),'MOMENTUM_20'=>$this->sign($f['momentum20']??0),'SMA20_VS_SMA50'=>$this->compare($f['sma20']??null,$f['sma50']??null),'QUANT_V1'=>(string)($f['quant_direction']??'NEUTRAL'),'AI_QUANT_V1'=>(string)($f['ai_direction']??'NEUTRAL'),default=>'NEUTRAL'}; }
    private function sign(float $v):string{return $v>0?'BULLISH':($v<0?'BEARISH':'NEUTRAL');} private function compare(?float $a,?float $b):string{return $a===null||$b===null?'NEUTRAL':$this->sign($a-$b);}
}
