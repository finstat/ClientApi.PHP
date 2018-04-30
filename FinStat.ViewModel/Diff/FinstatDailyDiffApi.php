<?php

require_once('../FinStat.Client/Requests.php');
require_once('../FinStat.Client/AbstractFinstatApi.php');
require_once('../FinStat.ViewModel/Diff/DailyDiff.php');
require_once('../FinStat.ViewModel/Diff/DailyDiffList.php');
require_once('../FinStat.ViewModel/Diff/KeyValue.php');


class FinstatDailyDiffApi extends AbstractFinstatApi
{
    public function RequestListOfDailyDiffs($json = false)
    {
        if(!class_exists('Requests'))
        {
            trigger_error("Unable to load Requests class", E_USER_WARNING);
            return false;
        }

        Requests::register_autoloader();

        $options = array(
            'timeout' => $this->timeout,
            'follow_redirects' => false,
            'auth' => false
        );

        $data = array(
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash(null),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl. "/GetListOfDiffs";

        try
        {
            $headers = null;
            if ($json) {
                $url = $url . ".json";
            }
            $response = Requests::post($url, $headers, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }


        $detail = $this->parseResponse($response, $url, null, $json);
        if($detail != false)
        {
            if(!$json)
            {
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

    private function ParseDailyDiff($detail)
    {
        if($detail != false)
        {
            $result = new DailyDiff();
            $result->FileName = (string) $detail->FileName;
            $result->FileSize = (int) $detail->FileSize;
            $result->GeneratedDate = $this->parseDate($detail->GeneratedDate);

            return $result;
        }

        return null;
    }

    public function DownloadDailyDiffFile($fileName, $exportPath)
    {
        if(!class_exists('Requests'))
        {
            trigger_error("Unable to load Requests class", E_USER_WARNING);
            return false;
        }

        Requests::register_autoloader();

        $options = array(
            'timeout' => $this->timeout,
            'follow_redirects' => false,
            'auth' => false
        );

        $data = array(
            'apiKey' => $this->apiKey,
            'fileName' => $fileName,
            'Hash' => $this->ComputeVerificationHash($fileName),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );
        $url = $this->apiUrl. "/GetFile";

        try
        {
            $response = Requests::post($url, null, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }

        $this->parseResponseRaw($response, $url, $fileName);

        return file_put_contents($exportPath, $response->body);
    }
}
