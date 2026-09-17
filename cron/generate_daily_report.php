<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/Database/Connection.php';require dirname(__DIR__).'/app/Reports/DailyReportViewModel.php';require dirname(__DIR__).'/app/Reports/DailyHtmlRenderer.php';require dirname(__DIR__).'/app/Reports/ReportArchive.php';require dirname(__DIR__).'/app/Reports/DailyReportService.php';require dirname(__DIR__).'/app/Reports/DailyReportDataBuilder.php';
require dirname(__DIR__).'/vendor/autoload.php';
use MarketForecast\Reports\{DailyReportService,DailyReportViewModel,DailyHtmlRenderer};
$date=$argv[1]??gmdate('Y-m-d');$archive=$argv[2]??throw new InvalidArgumentException('Archive directory required.');$db=$argv[3]??dirname(__DIR__).'/database/forecast.sqlite';$cycle=$argv[4]??'daily';$runKey=in_array($cycle,['stock','crypto'],true)?$cycle.'-'.$date:null;$data=(new MarketForecast\Reports\DailyReportDataBuilder(MarketForecast\Database\Connection::open($db)))->build($date,$runKey);$result=(new DailyReportService(new DailyReportViewModel(),new DailyHtmlRenderer()))->generate($data,$archive,$cycle);echo json_encode(['report_date'=>$date,'cycle'=>$cycle,'archive'=>$result['archive']],JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES),PHP_EOL;
