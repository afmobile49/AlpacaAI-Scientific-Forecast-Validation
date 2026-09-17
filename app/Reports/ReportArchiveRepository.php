<?php
declare(strict_types=1);
namespace MarketForecast\Reports;
use PDO;
final class ReportArchiveRepository
{
 public function __construct(private readonly PDO $pdo){}
 public function record(string $date,string $type,string $path,string $hash,string $generatedAt):bool{$s=$this->pdo->prepare('INSERT OR IGNORE INTO report_archives(report_date,report_type,html_path,content_sha256,generated_at) VALUES(?,?,?,?,?)');$s->execute([$date,$type,$path,$hash,$generatedAt]);return $s->rowCount()===1;}
}
