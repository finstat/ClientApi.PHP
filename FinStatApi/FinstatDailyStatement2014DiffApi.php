<?php
require_once(__DIR__ . '/../FinStat.Client/Requests.php');
require_once(__DIR__ . '/AbstractFinstatDiffApi.php');
require_once(__DIR__ . '/../FinStat.ViewModel/KeyValue.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Statement/StatementResult.php');

class FinstatDailyStatement2014DiffApi extends AbstractFinstatDailyDiffApi
{
    public function RequestListOfDailyStatementDiffs($json = false)
    {
        return $this->GetList("GetListOfStatement2014Diffs", $json);
    }

    public function DownloadDailyStatementDiffFile($fileName, $exportPath)
    {
        return $this->DownloadFile("GetStatement2014File", $fileName, $exportPath);
    }

    public function RequestStatementLegend($lang = "sk", $json = false)
    {
        $detail = $this->DoRequest("GetStatement2014Legend", array(
            'lang' => $lang,
        ), $lang, $json);

        if($detail != false)
        {
            if(!$json)
            {
                $result =  new StatementLegendResult();

                $result->Assets = array();
                foreach ($detail->Assets as $element) {
                    $o = new StatementLegendValue();
                    $o->ReportRow = (string)$element->Row;
                    $o->ReportSection = (string)$element->Section;
                    $o->Name = (string)$element->Name;
                    $result->Assets[] = $o;
                }

                $result->LiabilitiesAndEquity = array();
                foreach ($detail->LiabilitiesAndEquity as $element) {
                    $o = new StatementLegendValue();
                    $o->ReportRow = (string)$element->Row;
                    $o->ReportSection = (string)$element->Section;
                    $o->Name = (string)$element->Name;
                    $result->LiabilitiesAndEquity[] = $o;
                }

                $result->IncomeStatement = array();
                foreach ($detail->IncomeStatement as $element) {
                    $o = new StatementLegendValue();
                    $o->ReportRow = (string)$element->Row;
                    $o->ReportSection = (string)$element->Section;
                    $o->Name = (string)$element->Name;
                    $result->IncomeStatement[] = $o;
                }

                return $result;
            } else {
                return $detail;
            }
        }

        return null;
    }
}
