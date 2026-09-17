<?php
declare(strict_types=1);
namespace MarketForecast\AI;
final class PairedEligibility
{
 public static function eligible(?string $activationAt, string $quantGeneratedAt, string $aiGeneratedAt, bool $sameSnapshot): bool
 { return $activationAt!==null && $sameSnapshot && $quantGeneratedAt >= $activationAt && $aiGeneratedAt >= $activationAt; }
 public static function official(?string $activationAt,?int $pairId,bool $quantPresent,bool $aiPresent,bool $sameSnapshot,string $quantGeneratedAt,string $aiGeneratedAt):bool
 { return $pairId!==null && $quantPresent && $aiPresent && self::eligible($activationAt,$quantGeneratedAt,$aiGeneratedAt,$sameSnapshot); }
}
