<?php
declare(strict_types=1);
namespace MarketForecast\Reliability;
final class ReliabilityTrendDetector { public function detect(?float $short,?float $long,?float $shortEcs=null):array{if($short===null||$long===null)return ['label'=>'UNKNOWN','confidence'=>'LOW','delta'=>null];$d=$short-$long;$label=$d>=7?'IMPROVING':($d<=-7?'WEAKENING':'STABLE');return ['label'=>$label,'confidence'=>$shortEcs!==null&&$shortEcs<40?'LOW':'MODERATE','delta'=>$d];} }
