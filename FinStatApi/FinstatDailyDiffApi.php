<?php

require_once(__DIR__ . '/AbstractFinstatDiffApi.php');


class FinstatDailyDiffApi extends AbstractFinstatDailyDiffApi
{
    public function RequestListOfDailyDiffs($json = false)
    {
        return $this->GetList("GetListOfDiffs", $json);
    }

    public function DownloadDailyDiffFile($fileName, $exportPath)
    {
        return $this->DownloadFile("GetFile", $fileName, $exportPath);
    }
}
