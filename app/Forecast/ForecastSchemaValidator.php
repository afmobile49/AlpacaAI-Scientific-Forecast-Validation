<?php
declare(strict_types=1);
namespace MarketForecast\Forecast;
use InvalidArgumentException;
final class ForecastSchemaValidator
{
    private const DIRECTIONS=['BULLISH','NEUTRAL','BEARISH'];
    private const REASONS=['POSITIVE_TREND','NEGATIVE_TREND','POSITIVE_MOMENTUM','NEGATIVE_MOMENTUM','RSI_SUPPORTIVE','RSI_OVERBOUGHT','RSI_OVERSOLD','HIGH_VOLATILITY','LOW_VOLATILITY','VOLUME_CONFIRMATION','MIXED_SIGNAL','INSUFFICIENT_CONVICTION'];
    public function validate(array $p): array { foreach(['direction','probability_up','probability_neutral','probability_down','expected_return_pct','confidence','reason_codes','explanation'] as $k) if(!array_key_exists($k,$p))throw new InvalidArgumentException('Missing forecast field: '.$k); if(!in_array($p['direction'],self::DIRECTIONS,true))throw new InvalidArgumentException('Invalid direction.'); $probs=[$p['probability_up'],$p['probability_neutral'],$p['probability_down']]; foreach($probs as $v)if(!is_numeric($v)||$v<0||$v>1)throw new InvalidArgumentException('Invalid probability.'); if(abs(array_sum($probs)-1)>0.001||!is_numeric($p['confidence'])||$p['confidence']<0||$p['confidence']>1)throw new InvalidArgumentException('Invalid probabilities or confidence.'); if(!is_array($p['reason_codes'])||array_diff($p['reason_codes'],self::REASONS))throw new InvalidArgumentException('Invalid reason code.'); if(!is_string($p['explanation'])||strlen($p['explanation'])>1000)throw new InvalidArgumentException('Invalid explanation.'); return $p; }
}
