<?php
declare(strict_types=1);
namespace MarketForecast\Evaluation;
use DateTimeImmutable;
final class ReferencePriceRuleV2
{
 public const STOCK='REFERENCE_PRICE_RULE_V2';
 public const CRYPTO='CRYPTO_REFERENCE_PRICE_RULE_V1';
 public static function stockReference(array $regularSessionBar):float
 { if(!isset($regularSessionBar['open'])||!is_numeric($regularSessionBar['open'])||(float)$regularSessionBar['open']<=0)throw new \InvalidArgumentException('Invalid regular-session open.'); return (float)$regularSessionBar['open']; }
 public static function cryptoReference(array $bars,DateTimeImmutable $t0,int $toleranceMinutes=60):float
 { foreach($bars as $bar){$time=new DateTimeImmutable((string)$bar['time']);if($time>=$t0&&$time<=$t0->modify('+'.$toleranceMinutes.' minutes')){if(!isset($bar['close'])||!is_numeric($bar['close'])||(float)$bar['close']<=0)throw new \InvalidArgumentException('Invalid crypto reference close.');return (float)$bar['close'];}} throw new \RuntimeException('Crypto reference bar unavailable; retryable.'); }
}
