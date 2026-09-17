<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use PHPUnit\Framework\TestCase;
final class ReportsPageTest extends TestCase { public function testReportsPageEscapesOutput():void{$p=file_get_contents(dirname(__DIR__).'/public/reports.php');$r=file_get_contents(dirname(__DIR__).'/public/report.php');self::assertStringContainsString('lang="fa" dir="rtl"',$p);self::assertStringContainsString('htmlspecialchars',$p);self::assertStringContainsString('cycle=',$p);self::assertStringContainsString("\$suffix = \$cycle",$r);self::assertStringContainsString("report-'.\$date.\$suffix",$r);} }
