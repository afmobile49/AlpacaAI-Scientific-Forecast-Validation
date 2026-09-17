<?php
declare(strict_types=1);
namespace MarketForecast\Forecast;
final class QuantForecastEngine
{
    public function predict(array $features): array
    {
        $score=0.0;
        $score += $this->cmp($features,'ema20','ema50'); $score += $this->cmp($features,'close','sma20');
        $rsi=$features['rsi14']??null; if(is_numeric($rsi)){if($rsi>=50&&$rsi<=70)$score+=1;elseif($rsi>=30&&$rsi<50)$score-=1;elseif($rsi>75)$score-=.5;elseif($rsi<25)$score+=.5;}
        foreach(['momentum5','momentum20'] as $key) if(isset($features[$key])&&is_numeric($features[$key])) $score += $features[$key]>0?1:($features[$key]<0?-1:0);
        $direction=$score>=2?'BULLISH':($score<=-2?'BEARISH':'NEUTRAL'); $confidence=min(1.0,.5+abs($score)/10);
        $prob=$direction==='BULLISH'?[$confidence,(1-$confidence)*.4,(1-$confidence)*.6]:($direction==='BEARISH'?[(1-$confidence)*.6,(1-$confidence)*.4,$confidence]:[.3,.4,.3]);
        return ['direction'=>$direction,'score'=>$score,'probability_up'=>$prob[0],'probability_neutral'=>$prob[1],'probability_down'=>$prob[2],'confidence'=>$confidence,'algorithm_version'=>'QUANT_V1'];
    }
    private function cmp(array $f,string $a,string $b): float { if(!isset($f[$a],$f[$b])||!is_numeric($f[$a])||!is_numeric($f[$b]))return 0; return $f[$a]>$f[$b]?1:($f[$a]<$f[$b]?-1:0); }
}
