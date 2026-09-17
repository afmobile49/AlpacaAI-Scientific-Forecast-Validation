<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Reports\{EmailDeliveryService,RetryingEmailDeliveryService};
use PHPUnit\Framework\TestCase;
final class RetryingEmailDeliveryTest extends TestCase
{
 public function testRetriesUntilSent():void{$n=0;$s=new EmailDeliveryService(function()use(&$n){$n++;return ['status'=>$n<3?'FAILED':'SENT'];});$r=(new RetryingEmailDeliveryService($s,3,0))->send(['report_date'=>'2026-01-01'],'test@example.com');self::assertSame('SENT',$r['status']);self::assertSame(3,$r['attempts']);}
}
