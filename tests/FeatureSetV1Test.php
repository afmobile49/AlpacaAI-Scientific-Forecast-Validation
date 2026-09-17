<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Indicators\FeatureSetV1;use PHPUnit\Framework\TestCase;
final class FeatureSetV1Test extends TestCase{public function testAllApprovedKeysAreProduced():void{$b=[];for($i=0;$i<60;$i++)$b[]=['close'=>100+$i,'high'=>101+$i,'low'=>99+$i,'volume'=>1000+$i];$x=(new FeatureSetV1())->calculate($b);foreach(['sma20','sma50','ema20','ema50','rsi14','atr14','momentum5','momentum20','volatility20','distance_sma20','distance_sma50','volume_ratio20','feature_set_version'] as $k)self::assertArrayHasKey($k,$x);self::assertSame('FEATURE_SET_V1',$x['feature_set_version']);}}
