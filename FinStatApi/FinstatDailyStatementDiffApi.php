<?php
require_once(__DIR__ . '/AbstractFinstatDiffApi.php');
require_once(__DIR__ . '/../FinStat.ViewModel/KeyValue.php');

class FinstatDailyStatementDiffApi extends AbstractFinstatDailyDiffApi
{
    public function RequestListOfDailyStatementDiffs($json = false)
    {
        return $this->GetList("GetListOfStatementDiffs", $json);
    }

    public function DownloadDailyStatementDiffFile($fileName, $exportPath)
    {
        return $this->DownloadFile("GetStatementFile", $fileName, $exportPath);
    }


    public function RequestStatementLegend($lang = "sk", $json = false)
    {
        $detail = $this->DoRequest("GetStatementLegend", array(
            'lang' => $lang,
        ), $lang, $json);

        if($detail != false)
        {
            if(!$json)
            {
                $result = array();
                foreach ($detail->KeyValue as $element) {
                    $o = new KeyValue();
                    $o->Key = (string)$element->Key;
                    $o->Value = (string)$element->Value;
                    $result[] = $o;
                }

                return $result;
            } else {
                return $detail;
            }
        }

        return null;
    }
}
