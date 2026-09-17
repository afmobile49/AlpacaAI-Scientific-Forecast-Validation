<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Reliability\FrfCalculator;use PHPUnit\Framework\TestCase;
final class FrfCalculatorTest extends TestCase { public function testScoresAndLabels():void{$f=new FrfCalculator();$s=['direction'=>80,'probability'=>80,'calibration'=>80,'return_error'=>80,'benchmark_edge'=>80,'consistency'=>80];self::assertTrue(abs($f->fps($s)-80.0)<1e-9);self::assertTrue(abs($f->ecs(['sample_size'=>80,'time_coverage'=>80,'coverage'=>80,'regime_coverage'=>80,'data_quality'=>80])-80.0)<1e-9);self::assertTrue(abs($f->frs(80,80)-72.0)<1e-9);self::assertSame('GOOD',$f->label(80,'fps'));self::assertSame('RELIABLE',$f->label(72,'frs'));} public function testPartialScoresAreWeightedByAvailableEvidence():void{$f=new FrfCalculator();self::assertSame(50.0,$f->fps(['direction'=>50]));self::assertNull($f->frs(null,50));} }
