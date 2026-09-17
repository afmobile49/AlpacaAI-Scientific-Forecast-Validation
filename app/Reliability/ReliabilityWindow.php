<?php
declare(strict_types=1);
namespace MarketForecast\Reliability;
use DateTimeImmutable;
final class ReliabilityWindow { public function filter(array $rows,string $code,DateTimeImmutable $end):array{if($code==='CUMULATIVE')return array_values($rows);$days=['7D'=>7,'30D'=>30,'90D'=>90][$code]??0;$start=$end->modify('-'.$days.' days');return array_values(array_filter($rows,fn($r)=>isset($r['actual_price_time'])&&new DateTimeImmutable($r['actual_price_time'])>=$start&&new DateTimeImmutable($r['actual_price_time'])<=$end));} }
