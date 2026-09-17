<?php
declare(strict_types=1);
namespace MarketForecast\Reports;
final class DailyReportService { public function __construct(private readonly DailyReportViewModel $model,private readonly DailyHtmlRenderer $html){} public function generate(array $data,string $archiveDir,string $cycle='daily'):array{$v=$this->model->build($data);$html=$this->html->render($v);$archive=(new ReportArchive())->save($archiveDir,$v['report_date'],$html,$cycle);return ['view_model'=>$v,'archive'=>$archive];} }
