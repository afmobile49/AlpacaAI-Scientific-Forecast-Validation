<?php
declare(strict_types=1);
namespace MarketForecast\Evaluation;
use DateTimeImmutable;

/** Immutable price methodology: stocks use daily bar close; crypto uses first 1Min close at/after target, max 60 minutes. */
final class PriceRuleV1
{
 public const REFERENCE='REFERENCE_PRICE_RULE_V1';
 public const OUTCOME='OUTCOME_PRICE_RULE_V1';
 public const DIRECTION='OUTCOME_DIRECTION_RULE_V1';
 public static function stockReference(array $completedBar):float{return self::close($completedBar);}
 public static function stockOutcome(array $sessionCloseBar):float{return self::close($sessionCloseBar);}
 public static function cryptoTarget(DateTimeImmutable $reference,string $horizon):DateTimeImmutable{return match($horizon){'24H'=>$reference->modify('+24 hours'),'7D'=>$reference->modify('+7 days'),'30D'=>$reference->modify('+30 days'),default=>throw new \InvalidArgumentException('Unsupported crypto horizon.')};}
 public static function cryptoOutcome(array $bars,DateTimeImmutable $target,int $toleranceMinutes=60):float{foreach($bars as $bar){$time=new DateTimeImmutable((string)$bar['time']);if($time>=$target&&$time<=$target->modify('+'.$toleranceMinutes.' minutes'))return self::close($bar);}throw new \RuntimeException('Target-time crypto bar unavailable; retryable.');}
 private static function close(array $bar):float{if(!isset($bar['close'])||!is_numeric($bar['close'])||(float)$bar['close']<=0)throw new \InvalidArgumentException('Invalid deterministic close price.');return (float)$bar['close'];}
}
