<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use MarketForecast\Database\Connection;
use MarketForecast\Jobs\{JobLock,JobRunner};
if($argc<5)throw new InvalidArgumentException('Usage: run_with_job.php DB JOB_KEY JOB_NAME SCRIPT [args...]');
$db=$argv[1];$key=$argv[2];$name=$argv[3];$script=$argv[4];$args=array_slice($argv,5);$pdo=Connection::open($db);$runner=new JobRunner(new JobLock($pdo));$result=$runner->run($key,$name,function()use($script,$args){$cmd=escapeshellarg(PHP_BINARY).' '.escapeshellarg(dirname(__DIR__).'/cron/'.$script);foreach($args as $arg)$cmd.=' '.escapeshellarg($arg);passthru($cmd,$code);if($code!==0)throw new RuntimeException('Child job failed with exit code '.$code);return ['script'=>$script];},gmdate('c'));echo json_encode($result,JSON_THROW_ON_ERROR).PHP_EOL;
