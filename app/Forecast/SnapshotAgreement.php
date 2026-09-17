<?php
declare(strict_types=1);
namespace MarketForecast\Forecast;
use MarketForecast\Support\CanonicalHasher;

final class SnapshotAgreement
{
    public static function hash(array $features,string $cutoff):string{return CanonicalHasher::hash(['information_cutoff'=>$cutoff,'features'=>$features]);}
    public static function assertSame(array $quant,array $ai,string $cutoff):void{if(self::hash($quant,$cutoff)!==self::hash($ai,$cutoff))throw new \InvalidArgumentException('Quant and AI snapshots do not match.');}
}
