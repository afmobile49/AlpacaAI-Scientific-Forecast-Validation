<?php
declare(strict_types=1);
namespace MarketForecast\Reliability;
use DateTimeImmutable;
final class FrfWindowAggregator
{
 public function __construct(private readonly FrfCalculator $calculator=new FrfCalculator()){}
 public function calculate(array $records,DateTimeImmutable $end):array{$out=[];foreach(['CUMULATIVE'=>null,'ROLLING_90D'=>90,'ROLLING_30D'=>30,'ROLLING_7D'=>7] as $name=>$days){$items=array_values(array_filter($records,static function(array $r)use($end,$days){$d=new DateTimeImmutable($r['evaluated_at']??$r['actual_price_time']);return $days===null||$d>=$end->modify('-'.$days.' days');}));$n=count($items);$correct=array_sum(array_column($items,'direction_correct'));$brier=$n?array_sum(array_column($items,'brier_score'))/$n:null;$mae=$n?array_sum(array_column($items,'absolute_error_pct'))/$n:null;$scores=['direction'=>$n?$this->calculator->directionScore($correct,$n):null,'probability'=>$n?$this->calculator->probabilityScore((float)$brier,$n):null,'return_error'=>$mae===null?null:$this->calculator->returnErrorScore((float)$mae),'sample_size'=>$this->calculator->sampleSizeScore($n),'time_coverage'=>$n?100.0:0.0,'coverage'=>$n?100.0:0.0,'regime_coverage'=>$n?50.0:0.0,'data_quality'=>$n?100.0:0.0];$fps=$this->calculator->fps($scores);$ecs=$this->calculator->ecs($scores);$out[$name]=['resolved_n'=>$n,'fps'=>$fps,'ecs'=>$ecs,'frs'=>$this->calculator->frs($fps,$ecs),'diagnostics'=>['source'=>'RAW_EVALUATION_RECORDS','window'=>$name,'return_error_data'=>$mae===null?'NO_DATA':'AVAILABLE']];}return $out;}
}
