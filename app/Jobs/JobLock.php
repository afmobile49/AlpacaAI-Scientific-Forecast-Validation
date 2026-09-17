<?php
declare(strict_types=1);
namespace MarketForecast\Jobs;
use PDO;
final class JobLock
{
 public function __construct(private readonly PDO $pdo){}
 public function acquire(string $key,string $name,string $now):bool{$s=$this->pdo->prepare('INSERT OR IGNORE INTO system_jobs(job_key,job_name,status,started_at,metadata_json) VALUES(?,?,?,?,?)');$s->execute([$key,$name,'RUNNING',$now,'{}']);return $s->rowCount()===1;}
 public function finish(string $key,string $status,string $now,?string $error=null):void{if(!in_array($status,['SUCCESS','FAILED','SKIPPED'],true))throw new \InvalidArgumentException('Invalid terminal job status.');$s=$this->pdo->prepare('UPDATE system_jobs SET status=?,finished_at=?,error_message=? WHERE job_key=? AND status=\'RUNNING\'');$s->execute([$status,$now,$error,$key]);}
 public function recoverStale(string $before,string $now):int{$s=$this->pdo->prepare("UPDATE system_jobs SET status='FAILED',finished_at=?,error_message='STALE_RECOVERY' WHERE status='RUNNING' AND started_at<?");$s->execute([$now,$before]);return $s->rowCount();}
}
