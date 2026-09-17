<?php
declare(strict_types=1);
namespace MarketForecast\Reports;
final class ReportArchive { public function save(string $directory,string $date,string $html,string $cycle='daily'):array{$dir=rtrim($directory,"/\\");if(!is_dir($dir)&&!mkdir($dir,0775,true)&&!is_dir($dir))throw new \RuntimeException('Archive directory unavailable.');if(!preg_match('/^[a-z0-9_-]+$/i',$cycle))throw new \InvalidArgumentException('Invalid report cycle.');$hash=hash('sha256',$html);$suffix=$cycle==='daily'?'':'-'.$cycle;$path=$dir.'/report-'.$date.$suffix.'.html';if(file_exists($path))return ['path'=>$path,'hash'=>hash_file('sha256',$path),'created'=>false];file_put_contents($path,$html,LOCK_EX);return ['path'=>$path,'hash'=>$hash,'created'=>true];} }
