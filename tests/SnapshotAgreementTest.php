<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Forecast\SnapshotAgreement;
use PHPUnit\Framework\TestCase;
final class SnapshotAgreementTest extends TestCase
{
 public function testSameSnapshotIsAccepted():void{SnapshotAgreement::assertSame(['x'=>1],['x'=>1],'2026-01-01T00:00:00Z');self::assertSame(SnapshotAgreement::hash(['x'=>1],'2026-01-01T00:00:00Z'),SnapshotAgreement::hash(['x'=>1],'2026-01-01T00:00:00Z'));}
 public function testChangedFeatureIsRejected():void{$this->expectException(\InvalidArgumentException::class);SnapshotAgreement::assertSame(['x'=>1],['x'=>2],'2026-01-01T00:00:00Z');}
}
