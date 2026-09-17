<?php
declare(strict_types=1);

namespace MarketForecast\Tests;

use MarketForecast\Jobs\CyclePipeline;
use PHPUnit\Framework\TestCase;

final class CyclePipelineTest extends TestCase
{
    public function testReportAndEmailRunOnlyAfterFinalizedForecast(): void
    {
        $events = [];
        (new CyclePipeline())->run(
            static fn(): array => ['status' => 'FINALIZED'],
            static function () use (&$events): array { $events[] = 'report'; return ['status' => 'GENERATED']; },
            static function () use (&$events): array { $events[] = 'email'; return ['status' => 'SENT']; },
        );
        self::assertSame(['report', 'email'], $events);
    }

    public function testFailedForecastPreventsReportAndEmail(): void
    {
        $events = [];
        try {
            (new CyclePipeline())->run(
                static fn(): array => ['status' => 'ALREADY_FAILED'],
                static function () use (&$events): array { $events[] = 'report'; return []; },
                static function () use (&$events): array { $events[] = 'email'; return []; },
            );
            self::fail('Expected failed forecast to stop the pipeline.');
        } catch (\RuntimeException $error) {
            self::assertSame('Forecast cycle did not finalize.', $error->getMessage());
        }
        self::assertSame([], $events);
    }
}
