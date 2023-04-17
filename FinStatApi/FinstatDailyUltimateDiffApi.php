<?php

require_once(__DIR__ . '/AbstractFinstatDiffApi.php');

class FinstatDailyUltimateDiffApi extends AbstractFinstatDailyDiffApi
{
    public function RequestListOfDailyUltimateDiffs($json = false)
    {
        return $this->GetList("GetListOfUltimateDiffs", $json);
    }

    public function DownloadDailyUltimateDiffFile($fileName, $exportPath)
    {
        return $this->DownloadFile("GetUltimateFile", $fileName, $exportPath);
    }
}
