<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use DateTimeImmutable;
use MarketForecast\Forecast\InformationCutoff;
use PHPUnit\Framework\TestCase;
final class CentralInformationCutoffTest extends TestCase
{
 public function testOnlyBarsAtOrBeforeAuthoritativeCutoffAreAllowed():void{$c=new InformationCutoff(new DateTimeImmutable('2026-09-10T16:00:00Z'));self::assertTrue($c->allows(new DateTimeImmutable('2026-09-10T16:00:00Z')));self::assertTrue($c->allows(new DateTimeImmutable('2026-09-10T15:59:59Z')));self::assertFalse($c->allows(new DateTimeImmutable('2026-09-10T16:00:01Z')));self::assertSame('2026-09-10T16:00:00+00:00',$c->value());}
}
