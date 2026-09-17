<?php
declare(strict_types=1);
namespace MarketForecast\Jobs;
final class JobRunner
{
 public function __construct(private readonly JobLock $lock){}
 public function run(string $key,string $name,callable $work,string $now):array{if(!$this->lock->acquire($key,$name,$now))return ['status'=>'SKIPPED','job_key'=>$key];try{$result=$work();$this->lock->finish($key,'SUCCESS',$now);return ['status'=>'SUCCESS','job_key'=>$key,'result'=>$result];}catch(\Throwable $e){$this->lock->finish($key,'FAILED',$now,$e->getMessage());throw $e;}}
}
