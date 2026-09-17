<?php
declare(strict_types=1);
namespace MarketForecast\Reliability;
final class FrfCalculator
{
    public function directionScore(int $correct,int $resolved):?float{return $resolved>0?100*$correct/$resolved:null;}
    public function probabilityScore(float $brier,int $resolved):?float{return $resolved>0?max(0,min(100,100*(1-$brier))):null;}
    public function calibrationScore(float $ece,int $resolved):?float{return $resolved>0?max(0,min(100,100*(1-$ece))):null;}
    public function returnErrorScore(float $meanAbsoluteError):float{return max(0,min(100,100*(1-$meanAbsoluteError/10)));}
    public function benchmarkEdgeScore(float $edgePp):float{return max(0,min(100,50+$edgePp*10));}
    public function consistencyScore(float $stddev):float{return max(0,min(100,100*(1-$stddev/25)));}
    public function sampleSizeScore(int $n,int $target=100):float{return max(0,min(100,100*sqrt(max(0,$n)/max(1,$target))));}
    public function fps(array $scores): ?float { $weights=['direction'=>.25,'probability'=>.15,'calibration'=>.15,'return_error'=>.15,'benchmark_edge'=>.20,'consistency'=>.10];$sum=0;$weight=0;foreach($weights as $k=>$w)if(isset($scores[$k])&&is_numeric($scores[$k])){$sum+=$w*$scores[$k];$weight+=$w;}return $weight?max(0,min(100,$sum/$weight)):null; }
    public function ecs(array $scores): ?float { $weights=['sample_size'=>.35,'time_coverage'=>.20,'coverage'=>.15,'regime_coverage'=>.15,'data_quality'=>.15];$sum=0;$weight=0;foreach($weights as $k=>$w)if(isset($scores[$k])&&is_numeric($scores[$k])){$sum+=$w*$scores[$k];$weight+=$w;}return $weight?max(0,min(100,$sum/$weight)):null; }
    public function frs(?float $fps,?float $ecs): ?float { return $fps===null||$ecs===null?null:max(0,min(100,$fps*(.5+.5*$ecs/100))); }
    public function label(?float $score,string $type):string { if($score===null)return 'INSUFFICIENT_DATA'; return match($type){ 'fps'=>$score>=85?'EXCELLENT':($score>=70?'GOOD':($score>=55?'MODERATE':($score>=40?'WEAK':'POOR'))), 'ecs'=>$score>=80?'STRONG_EVIDENCE':($score>=60?'MODERATE_EVIDENCE':($score>=40?'LIMITED_EVIDENCE':'WEAK_EVIDENCE')), 'frs'=>$score>=85?'HIGH_RELIABILITY':($score>=70?'RELIABLE':($score>=55?'MODERATE':($score>=40?'PROVISIONAL':'UNRELIABLE'))), default=>'UNKNOWN'}; }
}
