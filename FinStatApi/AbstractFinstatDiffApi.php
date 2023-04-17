<?php

require_once(__DIR__ . '/../FinStat.Client/Requests.php');
require_once(__DIR__ . '/../FinStat.Client/AbstractFinstatApi.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Diff/DailyDiff.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Diff/DailyDiffList.php');


class AbstractFinstatDailyDiffApi extends AbstractFinstatApi
{
    protected function GetList($requestUrl, $json = false)
    {
        $detail = $this->DoRequest($requestUrl, array(), null, $json);

        if($detail != false) {
            if(!$json) {
                $result = new DailyDiffList();
                $result->Version = (string) $detail->Version;
                $result->Files = array();
                if (!empty($detail->Files) && isset($detail->Files->DailyDiff) && !empty($detail->Files->DailyDiff)) {
                    foreach ($detail->Files->DailyDiff as $element) {
                        $result->Files[] = $this->ParseDailyDiff($element);
                    }
                }

                return $result;
            } else {
                return $detail;
            }
        }

        return null;
    }

    protected function DownloadFile($requestUrl, $fileName, $exportPath)
    {
        $response = $this->DoBaseRequest($requestUrl, array('fileName' => $fileName), $fileName, false);

        $url = $this->apiUrl. $requestUrl;
        $this->parseResponseRaw($response, $url, $fileName);

        return file_put_contents($exportPath, $response->body);
    }

    protected function ParseDailyDiff($detail)
    {
        if($detail != false) {
            $result = new DailyDiff();
            $result->FileName = (string) $detail->FileName;
            $result->FileSize = (int) $detail->FileSize;
            $result->GeneratedDate = $this->parseDate($detail->GeneratedDate);
            $result->UploadDate = $this->parseDate($detail->UploadDate);

            return $result;
        }

        return null;
    }
}
