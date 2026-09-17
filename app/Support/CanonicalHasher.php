<?php
declare(strict_types=1);
namespace MarketForecast\Support;
final class CanonicalHasher{public static function hash(array $data):string{self::sort($data);return hash('sha256',json_encode($data,JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));}private static function sort(array &$a):void{ksort($a);foreach($a as &$v)if(is_array($v))self::sort($v);}}
