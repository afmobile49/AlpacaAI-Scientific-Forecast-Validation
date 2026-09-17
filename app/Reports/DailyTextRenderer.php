<?php
declare(strict_types=1);

namespace MarketForecast\Reports;

final class DailyTextRenderer
{
    public function render(array $view): string
    {
        $lines = [
            (string)($view['title'] ?? 'AlpacaAIApp'),
            'تاریخ: '.($view['report_date'] ?? gmdate('Y-m-d')),
            'آزمایش: '.($view['experiment'] ?? 'EXP-001'),
            '',
        ];
        foreach ($view['summary_cards'] ?? [] as $card) $lines[] = ($card['label'] ?? '—').': '.($card['value'] ?? '—');
        $pairs = $view['pair_summary'] ?? [];
        $lines[] = 'زوج‌های واجد شرایط: '.($pairs['eligible'] ?? 0);
        $lines[] = 'زوج‌های حل‌شده: '.($pairs['resolved'] ?? 0);
        $lines[] = '';
        $lines[] = 'FRF 2.0';
        if (($view['empty_states']['frf'] ?? null) !== null) $lines[] = $view['empty_states']['frf'];
        foreach ($view['health_rows'] ?? [] as $row) $lines[] = ($row['label'] ?? '—').': '.($row['value'] ?? '—');
        return implode("\n", $lines)."\n";
    }
}
