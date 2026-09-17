<?php
declare(strict_types=1);

namespace MarketForecast\Reports;

final class DailyReportViewModel
{
    public function build(array $data): array
    {
        $summary = $data['summary'] ?? [];
        $forecastRows = array_map(fn(array $row): array => $this->forecastRow($row), $data['forecasts'] ?? []);
        $frfRows = array_values($data['reliability_windows'] ?? []);
        $resolvedCount = (int)($summary['resolved_count'] ?? 0);

        return [
            'title' => 'گزارش روزانه پیش‌بینی بازار AlpacaAIApp',
            'report_date' => $data['report_date'] ?? gmdate('Y-m-d'),
            'experiment' => $data['experiment'] ?? 'EXP-001',
            'summary' => $summary,
            'summary_cards' => [
                ['label' => 'پیش‌بینی‌های امروز', 'value' => (string)($summary['forecast_count'] ?? 0)],
                ['label' => 'نتایج تکمیل‌شده', 'value' => (string)$resolvedCount],
                ['label' => 'دقت AI در زوج‌های حل‌شده', 'value' => $this->percentage($summary['ai_accuracy_pct'] ?? null)],
                ['label' => 'برتری AI نسبت به Quant', 'value' => $this->edge($summary['ai_edge_pp'] ?? null)],
            ],
            'pair_summary' => [
                'eligible' => (int)($summary['paired_eligible_count'] ?? 0),
                'resolved' => (int)($summary['paired_resolved_count'] ?? 0),
            ],
            'reliability' => $data['reliability'] ?? [],
            'reliability_windows' => $frfRows,
            'forecasts' => $forecastRows,
            'outcomes' => $data['outcomes'] ?? [],
            'benchmarks' => $data['benchmarks'] ?? [],
            'empty_states' => [
                'frf' => $frfRows === [] ? 'هنوز دادهٔ واقعی کافی برای محاسبهٔ FRF وجود ندارد.' : null,
                'outcomes' => $resolvedCount === 0 ? 'هنوز هیچ پیش‌بینی به زمان ارزیابی واقعی نرسیده است.' : null,
            ],
            'health_rows' => $this->healthRows($data['health'] ?? []),
            'health' => $data['health'] ?? [],
        ];
    }

    private function forecastRow(array $row): array
    {
        $source = (string)($row['source_type'] ?? 'QUANT');
        $direction = (string)($row['direction'] ?? 'NEUTRAL');
        $official = !empty($row['forecast_pair_id']);
        return $row + [
            'source_label' => $source === 'AI' ? 'AI' : 'Quant',
            'direction_label' => ['BULLISH' => 'صعودی', 'BEARISH' => 'نزولی', 'NEUTRAL' => 'خنثی'][$direction] ?? $direction,
            'pair_label' => $official ? 'زوج رسمی AI/Quant' : 'بدون زوج رسمی',
            'confidence_label' => isset($row['confidence']) ? (string)round((float)$row['confidence'] * 100) . '%' : '—',
            'expected_return_label' => isset($row['expected_return_pct']) && $row['expected_return_pct'] !== null ? sprintf('%+.2f%%', (float)$row['expected_return_pct']) : '—',
        ];
    }

    private function healthRows(array $health): array
    {
        return [
            ['label' => 'وضعیت سیستم', 'value' => ($health['status'] ?? '') === 'ok' ? 'سالم' : 'نیازمند بررسی'],
            ['label' => 'معامله‌گری واقعی', 'value' => !empty($health['trading_enabled']) ? 'فعال' : 'غیرفعال'],
            ['label' => 'Paper Trading', 'value' => !empty($health['paper_trading_enabled']) ? 'فعال' : 'غیرفعال'],
        ];
    }

    private function percentage(mixed $value): string
    {
        return $value === null ? 'در انتظار دادهٔ واقعی' : number_format((float)$value, 1) . '%';
    }

    private function edge(mixed $value): string
    {
        return $value === null ? 'در انتظار دادهٔ واقعی' : sprintf('%+.1f واحد درصد', (float)$value);
    }
}
