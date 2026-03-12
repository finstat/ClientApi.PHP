<?php

namespace FinStat\Api;

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
