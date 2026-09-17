<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Forecast\ForecastFreezer;
use PHPUnit\Framework\TestCase;
final class ForecastFreezerTest extends TestCase { public function testHashDetectsMutation():void{$f=(new ForecastFreezer())->freeze(['asset_id'=>1,'direction'=>'BULLISH','status'=>'DRAFT']);self::assertTrue((new ForecastFreezer())->verify($f));$f['direction']='BEARISH';self::assertFalse((new ForecastFreezer())->verify($f));} }
