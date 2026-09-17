<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use DateTimeImmutable;use MarketForecast\Reliability\FrfWindowAggregator;use PHPUnit\Framework\TestCase;
final class FrfWindowAggregatorTest extends TestCase
{
 public function testWindowsUseRawRecords():void{$r=[];foreach([1,10,40,100] as $days)$r[]=['evaluated_at'=>(new DateTimeImmutable('2026-09-10'))->modify("-$days days")->format('c'),'direction_correct'=>1,'brier_score'=>.1,'absolute_error_pct'=>1];$o=(new FrfWindowAggregator())->calculate($r,new DateTimeImmutable('2026-09-10'));foreach(['CUMULATIVE','ROLLING_90D','ROLLING_30D','ROLLING_7D'] as $w)self::assertArrayHasKey($w,$o);self::assertSame('RAW_EVALUATION_RECORDS',$o['CUMULATIVE']['diagnostics']['source']);}
}
