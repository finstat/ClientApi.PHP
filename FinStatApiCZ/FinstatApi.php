<?php

require_once('../FinStat.Client/Requests.php');
require_once('../FinStat.Client/AbstractFinstatApi.php');
require_once('../FinStat.Client/BaseFinstatApi.php');
require_once('../FinStat.Client/ViewModel/AutoCompleteResult.php');
require_once('../FinStatCZ.ViewModel/Detail/DetailResult.php');

class FinstatApi extends BaseFinstatApi
{
    public function RequestDetail($ico, $json = false)
    {
        return $this->Request($ico, 'detail', $json);
    }

    //
    // Requests the detail for specified ico
    //
    // Returns: details or FALSE
    public function Request($ico, $type="detail", $json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'ico' => $ico,
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash($ico),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl. $type;
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

        $detail = $this->parseResponse($response, $url, $ico, $json);
        if(!$json) {
            switch($type) {
                case 'detail':
                default:
                    $detail = $this->parseDetail($detail);
                    break;
            }
        }

        return $detail;
    }

    private function parseDetail($detail)
    {
        if  ($detail === FALSE) {
            return $detail;
        }

        $response = new DetailResult();
        $response = $this->parseAddress($detail, $response);
        $response->Ico                  = (string)$detail->Ico;
        $response->CzNaceCode           = (string)$detail->CzNaceCode;
        $response->CzNaceText           = (string)$detail->CzNaceText;
        $response->CzNaceDivision       = (string)$detail->CzNaceDivision;
        $response->CzNaceGroup          = (string)$detail->CzNaceGroup;
        $response->Name                 = (string)$detail->Name;
        $response->Created              = $this->parseDate($detail->Created);
        $response->Cancelled            = $this->parseDate($detail->Cancelled);
        $response->Activity             = (string)$detail->Activity;
        $response->Url                  = (string)$detail->Url;
        $response->Warning              = "{$detail->Warning}"  == 'true' ;
        $response->WarningUrl           = (string)$detail->WarningUrl;
        $response->LegalForm            = (string)$detail->LegalForm;
        $response->OwnershipType        = (string)$detail->OwnershipType;
        $response->EmployeeCount        = (string)$detail->EmployeeCount;

        return $response;
    }
}
