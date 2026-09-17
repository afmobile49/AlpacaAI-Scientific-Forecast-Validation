<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Support\CanonicalHasher;use PHPUnit\Framework\TestCase;
final class CanonicalHasherTest extends TestCase{public function testKeyOrderDoesNotChangeHash():void{self::assertSame(CanonicalHasher::hash(['b'=>2,'a'=>1]),CanonicalHasher::hash(['a'=>1,'b'=>2]));}}
