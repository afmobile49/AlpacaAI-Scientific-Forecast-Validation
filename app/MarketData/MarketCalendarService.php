<?php
declare(strict_types=1);
namespace MarketForecast\MarketData;
use DateTimeImmutable;
use DateTimeZone;
use DateInterval;
final class MarketCalendarService
{
    private DateTimeZone $marketTimezone;
    private array $holidays;
    private array $earlyCloseDates;
    public function __construct(?DateTimeZone $marketTimezone = null, ?array $holidays = null, ?array $earlyCloseDates = null) { $this->marketTimezone = $marketTimezone ?? new DateTimeZone('America/New_York'); $this->holidays=$holidays??['2026-01-01','2026-01-19','2026-02-16','2026-04-03','2026-05-25','2026-06-19','2026-07-03','2026-09-07','2026-11-26','2026-12-25','2027-01-01','2027-01-18','2027-02-15','2027-03-26','2027-05-31','2027-06-18','2027-07-05','2027-09-06','2027-11-25','2027-12-24'];$this->earlyCloseDates=$earlyCloseDates??['2026-11-27','2026-12-24','2027-11-26']; }
    public function isTradingSession(DateTimeImmutable $date): bool { $local = $date->setTimezone($this->marketTimezone); return (int)$local->format('N') < 6 && !in_array($local->format('Y-m-d'), $this->holidays, true); }
    public function sessionOpen(DateTimeImmutable $date): ?DateTimeImmutable { if (!$this->isTradingSession($date)) return null; $local=$date->setTimezone($this->marketTimezone); return $local->setTime(9,30)->setTimezone(new DateTimeZone('UTC')); }
    public function sessionClose(DateTimeImmutable $date): ?DateTimeImmutable { if (!$this->isTradingSession($date)) return null; $local=$date->setTimezone($this->marketTimezone); $minute=in_array($local->format('Y-m-d'),$this->earlyCloseDates,true)?13*60:16*60; return $local->setTime(intdiv($minute,60),$minute%60)->setTimezone(new DateTimeZone('UTC')); }
    public function isSessionComplete(DateTimeImmutable $at, DateTimeImmutable $session): bool { $close=$this->sessionClose($session); return $close!==null && $at->setTimezone(new DateTimeZone('UTC')) >= $close; }
    public function nextSessions(DateTimeImmutable $from, int $count): array { $result=[]; $cursor=$from->setTimezone($this->marketTimezone)->setTime(0,0); while(count($result)<$count){ if($this->isTradingSession($cursor)) $result[]=$cursor->setTimezone(new DateTimeZone('UTC')); $cursor=$cursor->add(new DateInterval('P1D')); } return $result; }
}
