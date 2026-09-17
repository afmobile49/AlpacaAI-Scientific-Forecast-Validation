<?php
declare(strict_types=1);

namespace MarketForecast\Reports;

final class DailyHtmlRenderer
{
    public function render(array $view): string
    {
        $e = static fn(mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $title = $e($view['title'] ?? 'گزارش روزانه پیش‌بینی بازار AlpacaAIApp');
        $date = $e($view['report_date'] ?? '—');
        $panelUrl = $e(getenv('PANEL_URL') ?: 'https://rayanhost.org/AlpacaAIApp/public/reports.php');
        $cards = '';
        foreach ($view['summary_cards'] ?? [] as $card) {
            $cards .= '<td width="25%" style="padding:4px;vertical-align:top"><table role="presentation" width="100%" style="border:1px solid #dfe5ee;border-radius:8px"><tr><td align="center" style="padding:12px 6px;font-size:12px;color:#526174">'.$e($card['label']).'<br><strong style="font-size:15px;color:#182234">'.$e($card['value']).'</strong></td></tr></table></td>';
        }
        $frf = $this->frf($view, $e);
        $forecasts = $this->forecasts($view['forecasts'] ?? [], $e);
        $outcomes = $this->outcomes($view, $e);
        $health = '';
        foreach ($view['health_rows'] ?? [] as $row) {
            $health .= '<tr><td style="padding:7px;border-bottom:1px solid #e7ebf0">'.$e($row['label']).'</td><td style="padding:7px;border-bottom:1px solid #e7ebf0;color:#087f44;font-weight:bold">'.$e($row['value']).'</td></tr>';
        }
        $pairs = $view['pair_summary'] ?? [];
        return '<!doctype html><html lang="fa" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.$title.'</title></head><body style="margin:0;background:#f1f4f8;color:#182234;font-family:Tahoma,Arial,sans-serif;direction:rtl"><table role="presentation" width="100%" style="background:#f1f4f8"><tr><td style="padding:14px"><table role="presentation" width="680" align="center" style="width:100%;max-width:680px"><tr><td style="background:#111827;border-radius:12px;padding:20px;color:#fff"><h1 style="margin:0 0 8px;font-size:21px">'.$title.'</h1><div style="font-size:12px">گزارش خودکار روزانه — <span dir="ltr">'.$date.'</span></div></td></tr><tr><td style="height:10px"></td></tr><tr><td style="background:#fff;border-radius:12px;padding:12px"><table role="presentation" width="100%"><tr>'.$cards.'</tr></table><p style="margin:12px 4px 0;font-size:12px">زوج‌های واجد شرایط: <span dir="ltr">'.$e($pairs['eligible'] ?? 0).'</span> | زوج‌های حل‌شده: <span dir="ltr">'.$e($pairs['resolved'] ?? 0).'</span></p></td></tr><tr><td style="height:10px"></td></tr>'.$frf.'<tr><td style="height:10px"></td></tr><tr><td style="background:#fff;border-radius:12px;padding:18px"><h2 style="margin:0 0 12px;font-size:17px">پیش‌بینی‌های مهم امروز</h2><table role="presentation" width="100%" style="border-collapse:collapse;font-size:12px"><tr style="color:#526174"><th style="padding:8px;border-bottom:1px solid #dfe5ee;text-align:right">دارایی</th><th style="padding:8px;border-bottom:1px solid #dfe5ee;text-align:right">منبع</th><th style="padding:8px;border-bottom:1px solid #dfe5ee;text-align:right">افق</th><th style="padding:8px;border-bottom:1px solid #dfe5ee;text-align:right">پیش‌بینی</th><th style="padding:8px;border-bottom:1px solid #dfe5ee;text-align:right">اعتماد</th><th style="padding:8px;border-bottom:1px solid #dfe5ee;text-align:right">بازدهٔ مورد انتظار</th><th style="padding:8px;border-bottom:1px solid #dfe5ee;text-align:right">وضعیت</th></tr>'.$forecasts.'</table></td></tr><tr><td style="height:10px"></td></tr>'.$outcomes.'<tr><td style="height:10px"></td></tr><tr><td style="background:#fff;border-radius:12px;padding:18px"><h2 style="margin:0 0 8px;font-size:17px">جمع‌بندی روش</h2><p style="margin:0;line-height:1.9;font-size:13px">این گزارش، پیش‌بینی‌های نهایی و تغییرناپذیر Quant و AI را فقط بر اساس داده‌های ثبت‌شده نمایش می‌دهد. شاخص‌های دقت و برتری AI تنها پس از رسیدن قیمت واقعی در سررسید و ارزیابی زوج‌های رسمی محاسبه می‌شوند.</p></td></tr><tr><td style="height:10px"></td></tr><tr><td style="background:#fff;border-radius:12px;padding:18px"><h2 style="margin:0 0 10px;font-size:17px">وضعیت سلامت سیستم</h2><table role="presentation" width="100%" style="border-collapse:collapse;font-size:13px">'.$health.'</table><p style="font-size:12px;line-height:1.8">این گزارش صرفاً برای ارزیابی روش پیش‌بینی است و توصیه سرمایه‌گذاری نیست.</p><p style="margin:18px 0 4px"><a href="'.$panelUrl.'" style="display:inline-block;background:#1769e0;color:#fff;text-decoration:none;border-radius:7px;padding:11px 16px">مشاهدهٔ پنل کاربری و گزارش‌ها</a></p></td></tr></table></td></tr></table></body></html>';
    }

    private function frf(array $view, callable $e): string
    {
        if (($view['empty_states']['frf'] ?? null) !== null) {
            return '<tr><td style="background:#fff;border-radius:12px;padding:18px"><h2 style="margin:0 0 8px;font-size:17px"><span dir="ltr">Forecast Reliability Framework 2.0</span></h2><p style="margin:0;color:#657487;font-size:13px">'.$e($view['empty_states']['frf']).'</p></td></tr>';
        }
        $rows = '';
        foreach ($view['reliability_windows'] ?? [] as $window => $row) {
            $rows .= '<tr><td style="padding:8px;border-bottom:1px solid #e7ebf0"><span dir="ltr">'.$e($window).'</span></td><td style="padding:8px;border-bottom:1px solid #e7ebf0">'.$e($row['fps'] ?? '—').'</td><td style="padding:8px;border-bottom:1px solid #e7ebf0">'.$e($row['ecs'] ?? '—').'</td><td style="padding:8px;border-bottom:1px solid #e7ebf0">'.$e($row['frs'] ?? '—').'</td><td style="padding:8px;border-bottom:1px solid #e7ebf0">'.$e($row['resolved_n'] ?? 0).'</td></tr>';
        }
        return '<tr><td style="background:#fff;border-radius:12px;padding:18px"><h2 style="margin:0 0 10px;font-size:17px"><span dir="ltr">Forecast Reliability Framework 2.0</span></h2><table role="presentation" width="100%" style="border-collapse:collapse;font-size:12px"><tr><th>پنجره</th><th><span dir="ltr">FPS</span></th><th><span dir="ltr">ECS</span></th><th><span dir="ltr">FRS</span></th><th>نمونه</th></tr>'.$rows.'</table></td></tr>';
    }

    private function forecasts(array $forecasts, callable $e): string
    {
        if ($forecasts === []) return '<tr><td colspan="7" style="padding:12px;color:#657487">پیش‌بینی نهایی برای این چرخه وجود ندارد.</td></tr>';
        $rows = '';
        foreach ($forecasts as $forecast) {
            $rows .= '<tr><td style="padding:8px;border-bottom:1px solid #e7ebf0"><span dir="ltr">'.$e($forecast['symbol'] ?? '—').'</span></td><td style="padding:8px;border-bottom:1px solid #e7ebf0"><span dir="ltr">'.$e($forecast['source_label'] ?? $forecast['source_type'] ?? '—').'</span></td><td style="padding:8px;border-bottom:1px solid #e7ebf0"><span dir="ltr">'.$e($forecast['horizon_code'] ?? '—').'</span></td><td style="padding:8px;border-bottom:1px solid #e7ebf0">'.$e($forecast['direction_label'] ?? $forecast['direction'] ?? '—').'</td><td style="padding:8px;border-bottom:1px solid #e7ebf0"><span dir="ltr">'.$e($forecast['confidence_label'] ?? '—').'</span></td><td style="padding:8px;border-bottom:1px solid #e7ebf0"><span dir="ltr">'.$e($forecast['expected_return_label'] ?? '—').'</span></td><td style="padding:8px;border-bottom:1px solid #e7ebf0">'.$e($forecast['pair_label'] ?? 'بدون زوج رسمی').'</td></tr>';
        }
        return $rows;
    }

    private function outcomes(array $view, callable $e): string
    {
        if (($view['empty_states']['outcomes'] ?? null) !== null) {
            return '<tr><td style="background:#fff;border-radius:12px;padding:18px"><h2 style="margin:0 0 8px;font-size:17px">نتایجی که امروز نهایی شدند</h2><p style="margin:0;color:#657487;font-size:13px">'.$e($view['empty_states']['outcomes']).'</p></td></tr>';
        }
        return '<tr><td style="background:#fff;border-radius:12px;padding:18px"><h2 style="margin:0;font-size:17px">نتایجی که امروز نهایی شدند</h2></td></tr>';
    }
}
