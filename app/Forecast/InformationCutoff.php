<?php
declare(strict_types=1);
namespace MarketForecast\Forecast;
use DateTimeImmutable;
final class InformationCutoff
{
 public function __construct(public readonly DateTimeImmutable $at){}
 public function allows(DateTimeImmutable $bar):bool{return $bar <= $this->at;}
 public function value():string{return $this->at->format('c');}
}
