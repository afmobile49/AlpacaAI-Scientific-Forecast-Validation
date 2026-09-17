<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Reports\{DailyReportService,DailyReportViewModel,DailyHtmlRenderer};use PHPUnit\Framework\TestCase;
final class DailyReportServiceTest extends TestCase { public function testGenerationArchivesOnce():void{$d=sys_get_temp_dir().'/daily-'.bin2hex(random_bytes(3));$s=new DailyReportService(new DailyReportViewModel(),new DailyHtmlRenderer());$a=$s->generate(['report_date'=>'2026-01-01','health'=>['status'=>'ok']],$d);$b=$s->generate(['report_date'=>'2026-01-01','health'=>['status'=>'changed']],$d);self::assertTrue($a['archive']['created']);self::assertFalse($b['archive']['created']);@unlink($a['archive']['path']);@rmdir($d);} }
