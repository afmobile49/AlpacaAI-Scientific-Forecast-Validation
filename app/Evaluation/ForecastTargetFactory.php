<?php
declare(strict_types=1);
namespace MarketForecast\Evaluation;
use DateTimeImmutable;
use MarketForecast\MarketData\MarketCalendarService;
final class ForecastTargetFactory
{
 public function __construct(private readonly ?MarketCalendarService $calendar=null){}
 public function create(string $assetType,DateTimeImmutable $referenceTime,string $horizon='1D'):array{$type=strtoupper($assetType);if($type==='STOCK'){if(!in_array($horizon,['1D','5D','20D'],true))throw new \InvalidArgumentException('Unsupported stock horizon.');$n=(int)substr($horizon,0,-1);$cal=$this->calendar??new MarketCalendarService();$sessions=$cal->nextSessions($referenceTime,$n+1);$close=$cal->sessionClose($sessions[$n]);return ['target_time'=>$close->format('c'),'target_rule'=>PriceRuleV1::OUTCOME.':STOCK_'.$horizon,'status'=>'PENDING'];}if(!in_array($type,['CRYPTO','TOKENIZED_GOLD'],true)||!in_array($horizon,['1D','24H','7D','30D'],true))throw new \InvalidArgumentException('Unsupported target.');$normalized=$horizon==='1D'?'24H':$horizon;$hours=$normalized==='24H'?24:($normalized==='7D'?168:720);return ['target_time'=>$referenceTime->modify('+'.$hours.' hours')->format('c'),'target_rule'=>PriceRuleV1::OUTCOME.':CRYPTO_'.$normalized,'status'=>'PENDING'];}
}
