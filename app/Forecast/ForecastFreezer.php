<?php
declare(strict_types=1);
namespace MarketForecast\Forecast;
use InvalidArgumentException;
final class ForecastFreezer
{
    public function freeze(array $payload): array { if(($payload['status']??'')==='FINAL')throw new InvalidArgumentException('Forecast is already final.'); $payload['status']='FINAL'; $payload['forecast_hash']=$this->hash($payload); return $payload; }
    public function hash(array $payload): string { unset($payload['forecast_hash']); ksort($payload); return hash('sha256',json_encode($payload,JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES)); }
    public function verify(array $payload): bool { return isset($payload['forecast_hash'])&&hash_equals($payload['forecast_hash'],$this->hash($payload)); }
}
