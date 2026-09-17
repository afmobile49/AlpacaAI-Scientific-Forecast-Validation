<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Reports\{DailyReportViewModel,DailyHtmlRenderer};
use PHPUnit\Framework\TestCase;
final class DailyReportTest extends TestCase
{
    public function testSharedModelAndEscaping(): void
    {
        $v = (new DailyReportViewModel())->build(['report_date'=>'2026-01-01','reliability'=>['frs'=>75],'health'=>['status'=>'<ok>']]);
        $html = (new DailyHtmlRenderer())->render($v);
        self::assertStringContainsString('گزارش روزانه پیش‌بینی بازار AlpacaAIApp', $html);
        self::assertStringNotContainsString('<ok>', $html);
        self::assertStringNotContainsString('status', $html);
    }

    public function testRendererDisclosesSourceAndTruthfulNoDataStates(): void
    {
        $v = (new DailyReportViewModel())->build([
            'report_date'=>'2026-09-16',
            'summary'=>['forecast_count'=>2,'resolved_count'=>0,'paired_eligible_count'=>1,'paired_resolved_count'=>0,'ai_accuracy_pct'=>null,'ai_edge_pp'=>null],
            'forecasts'=>[
                ['symbol'=>'BTC/USD','source_type'=>'AI','horizon_code'=>'24H','direction'=>'NEUTRAL','confidence'=>.61,'expected_return_pct'=>.2,'forecast_pair_id'=>7],
                ['symbol'=>'BTC/USD','source_type'=>'QUANT','horizon_code'=>'24H','direction'=>'BEARISH','confidence'=>.8,'expected_return_pct'=>-1.2,'forecast_pair_id'=>7],
            ],
            'reliability_windows'=>[],
            'health'=>['status'=>'ok','trading_enabled'=>false,'paper_trading_enabled'=>false],
        ]);
        $html = (new DailyHtmlRenderer())->render($v);
        self::assertStringContainsString('منبع', $html);
        self::assertStringContainsString('بازدهٔ مورد انتظار', $html);
        self::assertStringContainsString('هنوز دادهٔ واقعی کافی برای محاسبهٔ FRF وجود ندارد.', $html);
        self::assertStringContainsString('زوج رسمی AI/Quant', $html);
        self::assertStringContainsString('وضعیت سیستم', $html);
        self::assertStringContainsString('معامله‌گری واقعی', $html);
        self::assertStringContainsString('https://rayanhost.org/AlpacaAIApp/public/reports.php', $html);
        self::assertStringNotContainsString('<pre>{', $html);
    }
}
