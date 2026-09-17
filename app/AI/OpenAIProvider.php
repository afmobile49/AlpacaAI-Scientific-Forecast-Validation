<?php
declare(strict_types=1);
namespace MarketForecast\AI;
use MarketForecast\Forecast\ForecastSchemaValidator;
use RuntimeException;
final class OpenAIProvider implements AiProviderInterface
{
    private readonly mixed $transport;
    public function __construct(private readonly string $apiKey, private readonly string $model='gpt-5.6', private readonly string $baseUrl='https://api.openai.com', mixed $transport=null) { $this->transport=$transport; }
    public function forecast(array $features, string $informationCutoff): array
    {
        $input='Return only JSON with direction exactly one of BULLISH, NEUTRAL, BEARISH; probability_up, probability_neutral, probability_down; expected_return_pct; confidence; reason_codes selected only from POSITIVE_TREND, NEGATIVE_TREND, POSITIVE_MOMENTUM, NEGATIVE_MOMENTUM, RSI_SUPPORTIVE, RSI_OVERBOUGHT, RSI_OVERSOLD, HIGH_VOLATILITY, LOW_VOLATILITY, VOLUME_CONFIRMATION, MIXED_SIGNAL, INSUFFICIENT_CONVICTION; and explanation. Use only these features; information cutoff: '.$informationCutoff.'. Features: '.json_encode($features,JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);
        $payload=['model'=>$this->model,'store'=>false,'input'=>[['role'=>'system','content'=>'You are an auditable market forecast component. Do not use external or future data. Return strict JSON only.'],['role'=>'user','content'=>$input]]];
        $body=$this->transport ? ($this->transport)($payload,$this->apiKey) : $this->request($payload);
        $text=$body['output_text']??null; if(!is_string($text)&&isset($body['output'][0]['content'][0]['text']))$text=$body['output'][0]['content'][0]['text'];
        if(!is_string($text))throw new RuntimeException('OpenAI response has no text output.');
        $decoded=json_decode($text,true); if(!is_array($decoded))throw new RuntimeException('OpenAI output is not JSON.');
        $decoded=$this->normalizeObservedAliases($decoded);
        return (new ForecastSchemaValidator())->validate($decoded);
    }
    private function normalizeObservedAliases(array $forecast): array { $directions=['up'=>'BULLISH','bullish'=>'BULLISH','down'=>'BEARISH','bearish'=>'BEARISH','neutral'=>'NEUTRAL','flat'=>'NEUTRAL'];$reasons=['CLOSE_ABOVE_SMAS'=>'POSITIVE_TREND','EMA_UPTREND'=>'POSITIVE_TREND','CLOSE_BELOW_SMAS'=>'NEGATIVE_TREND','EMA_DOWNTREND'=>'NEGATIVE_TREND','RSI_BULLISH'=>'RSI_SUPPORTIVE','RSI_BEARISH'=>'RSI_SUPPORTIVE'];$direction=strtolower(trim((string)($forecast['direction']??'')));if(isset($directions[$direction]))$forecast['direction']=$directions[$direction];if(isset($forecast['reason_codes'])&&is_array($forecast['reason_codes']))$forecast['reason_codes']=array_map(static fn($reason)=>$reasons[(string)$reason]??$reason,$forecast['reason_codes']);return $forecast; }
    private function request(array $payload): array { $ch=curl_init(rtrim($this->baseUrl,'/').'/v1/responses'); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($payload,JSON_THROW_ON_ERROR),CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$this->apiKey,'Content-Type: application/json']]); $raw=curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);if($raw===false||$status<200||$status>=300)throw new RuntimeException('OpenAI request failed: HTTP '.$status);$result=json_decode((string)$raw,true);if(!is_array($result))throw new RuntimeException('Invalid OpenAI JSON.');return $result; }
}
