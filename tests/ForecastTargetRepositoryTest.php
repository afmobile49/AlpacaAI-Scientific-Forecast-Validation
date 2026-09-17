<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use DateTimeImmutable;
use MarketForecast\Evaluation\ForecastTargetRepository;
use MarketForecast\Database\Connection;
use PHPUnit\Framework\TestCase;

final class ForecastTargetRepositoryTest extends TestCase
{
    public function testTargetIsIdempotent(): void
    {
        $p=tempnam(sys_get_temp_dir(),'target-');$db=Connection::open($p);
        $db->exec('CREATE TABLE forecast_targets(id INTEGER PRIMARY KEY AUTOINCREMENT,final_prediction_id INTEGER,horizon_code TEXT,target_time TEXT,target_rule TEXT,status TEXT,resolved_at TEXT,UNIQUE(final_prediction_id,horizon_code,target_rule))');
        $r=new ForecastTargetRepository($db);$t=new DateTimeImmutable('2026-01-01T12:00:00Z');
        self::assertNotSame(0,$r->create(7,'CRYPTO',$t)); self::assertNotSame(0,$r->create(7,'CRYPTO',$t));
        self::assertSame(1,(int)$db->query('SELECT count(*) FROM forecast_targets')->fetchColumn());@unlink($p);
    }
}
