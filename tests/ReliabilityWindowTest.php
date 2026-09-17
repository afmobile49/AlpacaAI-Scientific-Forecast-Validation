<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use DateTimeImmutable;use MarketForecast\Reliability\{ReliabilityWindow,ReliabilityTrendDetector};use PHPUnit\Framework\TestCase;
final class ReliabilityWindowTest extends TestCase { public function testRollingWindowFiltersResolvedOutcomes():void{$rows=[['actual_price_time'=>'2026-09-10T12:00:00Z'],['actual_price_time'=>'2026-08-01T12:00:00Z']];self::assertCount(1,(new ReliabilityWindow())->filter($rows,'30D',new DateTimeImmutable('2026-09-11T00:00:00Z')));self::assertCount(2,(new ReliabilityWindow())->filter($rows,'CUMULATIVE',new DateTimeImmutable('2026-09-11T00:00:00Z')));} public function testTrendRules():void{$t=new ReliabilityTrendDetector();self::assertSame('IMPROVING',$t->detect(80,70)['label']);self::assertSame('WEAKENING',$t->detect(60,70,30)['label']);self::assertSame('LOW',$t->detect(60,70,30)['confidence']);} }
