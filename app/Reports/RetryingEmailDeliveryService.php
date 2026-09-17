<?php
declare(strict_types=1);
namespace MarketForecast\Reports;
final class RetryingEmailDeliveryService
{
 public function __construct(private readonly EmailDeliveryService $service,private readonly int $maxAttempts=3,private readonly int $baseDelaySeconds=2){}
 public function send(array $view,string $recipient):array{$last=[];for($i=1;$i<=$this->maxAttempts;$i++){try{$last=$this->service->send($view,$recipient);if(($last['status']??'')==='SENT'){$last['attempts']=$i;return $last;}}catch(\Throwable $e){$last=['status'=>'FAILED','reason'=>$e->getMessage()];}if($i<$this->maxAttempts)usleep($this->baseDelaySeconds*($i-1)*100000);}$last['attempts']=$this->maxAttempts;return $last;}
}
