<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use InvalidArgumentException;
use MarketForecast\Forecast\ForecastSchemaValidator;
use PHPUnit\Framework\TestCase;
final class ForecastSchemaValidatorTest extends TestCase { private function valid():array{return ['direction'=>'BULLISH','probability_up'=>.7,'probability_neutral'=>.2,'probability_down'=>.1,'expected_return_pct'=>2.1,'confidence'=>.75,'reason_codes'=>['POSITIVE_TREND'],'explanation'=>'audit'];} public function testValid():void{self::assertSame($this->valid(),(new ForecastSchemaValidator())->validate($this->valid()));} public function testInvalidProbabilityIsRejected():void{$p=$this->valid();$p['probability_up']=1.2;$this->expectException(InvalidArgumentException::class);(new ForecastSchemaValidator())->validate($p);} }
