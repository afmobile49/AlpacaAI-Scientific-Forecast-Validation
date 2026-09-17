<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use DateTimeImmutable;
use DateTimeZone;
use MarketForecast\MarketData\MarketCalendarService;
use PHPUnit\Framework\TestCase;
final class MarketCalendarTest extends TestCase
{
    public function testWeekendAndHolidayAreNotSessions(): void { $c=new MarketCalendarService(null,['2026-09-14']); self::assertFalse($c->isTradingSession(new DateTimeImmutable('2026-09-12 12:00',new DateTimeZone('UTC')))); self::assertFalse($c->isTradingSession(new DateTimeImmutable('2026-09-14 12:00',new DateTimeZone('UTC')))); }
    public function testDefaultCalendarContainsVersionedNyseHolidayAndEarlyClose(): void { $c=new MarketCalendarService(); self::assertFalse($c->isTradingSession(new DateTimeImmutable('2026-11-26 17:00',new DateTimeZone('UTC')))); self::assertSame('18:00',$c->sessionClose(new DateTimeImmutable('2026-11-27 17:00',new DateTimeZone('UTC')))->format('H:i')); }
    public function testOpenAndEarlyCloseConvertToUtc(): void { $c=new MarketCalendarService(null,[],['2026-11-27']); self::assertSame('14:30',$c->sessionOpen(new DateTimeImmutable('2026-11-27 12:00',new DateTimeZone('UTC')))->format('H:i')); self::assertSame('18:00',$c->sessionClose(new DateTimeImmutable('2026-11-27 12:00',new DateTimeZone('UTC')))->format('H:i')); }
    public function testCountsTradingSessions(): void { $c=new MarketCalendarService(null,['2026-09-14']); $s=$c->nextSessions(new DateTimeImmutable('2026-09-11 12:00',new DateTimeZone('UTC')),3); self::assertSame(['2026-09-11','2026-09-15','2026-09-16'],array_map(fn($d)=>$d->format('Y-m-d'),$s)); }
}
