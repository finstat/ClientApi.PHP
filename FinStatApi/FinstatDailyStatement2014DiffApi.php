<?php

namespace FinStat\Api;

use FinStat\ViewModel\KeyValue;
use FinStat\ViewModel\Statement\StatementResult;
use FinStat\ViewModel\Statement\StatementLegendResult;
use FinStat\ViewModel\Statement\StatementLegendValue;

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

        if ($detail !== false) {
            if(!$json) {
                $result =  new StatementLegendResult();

                $result->Assets = $this->parseStatementLegendValues($detail->Assets);
                $result->LiabilitiesAndEquity = $this->parseStatementLegendValues($detail->LiabilitiesAndEquity);
                $result->IncomeStatement = $this->parseStatementLegendValues($detail->IncomeStatement);

                return $result;
            } else {
                return $detail;
            }
        }

        return null;
    }
}
