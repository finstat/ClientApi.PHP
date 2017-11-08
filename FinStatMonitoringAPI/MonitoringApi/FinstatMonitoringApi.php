<?php

require_once('Requests.php');
require_once('MonitoringReportResult.php');
require_once('ProceedingResult.php');

class FinstatMonitoringApi
{
    private
        $apiUrl,
        $apiKey,
        $privateKey,
        $stationId,
        $stationName,
        $timeout,
        $limits;

    //
    // Constructor
    //
    public function __construct($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout = 10)
    {
        if (!empty($apiUrl) && strpos($apiUrl, "localhost") == false) {
            if(strpos($apiUrl, "http://") !== false) {
                $apiUrl = str_replace("http://", "https://", $apiUrl);
            }
            if(strpos($apiUrl, "https://") === false) {
                $apiUrl = "https://" . $apiUrl;
            }
        }
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
        $this->stationId = $stationId;
        $this->privateKey = $privateKey;
        $this->stationName = $stationName;
        $this->timeout = $timeout;
        $this->limits = null;
    }

    private function InitRequests()
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

        return $options;
    }

    public function AddToMonitoring($ico, $json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'ico' => $ico,
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash($ico),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "AddToMonitoring";
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

        $parse = (string)$detail;
        return ($json) ? $detail : ($parse == 'true');
    }

    public function AddDateToMonitoring($date, $json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'date' => $date,
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash($date),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "AddDateToMonitoring";
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

        $detail = $this->parseResponse($response, $url, $date, $json);

        $parse = (string)$detail;
        return ($json) ? $detail : ($parse == 'true');
    }

    private function parseResponse($response, $url, $parameter = null, $json = false)
    {
        //parse limits
        $this->limits = array(
            "daily" => array(
                "current" => ($response->headers->offsetExists('finstat-daily-limit-current')) ? $response->headers->offsetGet('finstat-daily-limit-current') : null,
                "max"=> ($response->headers->offsetExists('finstat-daily-limit-max')) ? $response->headers->offsetGet('finstat-daily-limit-max') : null
            ),
            "monthly" => array(
                "current" => ($response->headers->offsetExists('finstat-monthly-limit-current')) ? $response->headers->offsetGet('finstat-monthly-limit-current') : null,
                "max"=> ($response->headers->offsetExists('finstat-monthly-limit-max')) ? $response->headers->offsetGet('finstat-monthly-limit-max') : null
            ),
        );

        if(!$response->success)
        {
            $dom = new DOMDocument();
            $dom->loadHTML($response->body);
            switch($response->status_code)
            {
                case 404:
                    if(isset($parameter) && !empty($parameter)) {
                        throw new Requests_Exception("Invalid URL: '{$url}' or specified parameter: '{$parameter}' not found in database!", 'FinstatApi', $dom->textContent, $response->status_code);
                    } else {
                        throw new Requests_Exception("Invalid URL: '{$url}'!", 'FinstatApi', $dom->textContent, $response->status_code);
                    }

                case 402:
                    throw new Requests_Exception('Limit reached!', 'FinstatApi', $dom->textContent, $response->status_code);

                case 403:
                    throw new Requests_Exception('Access Forbidden!', 'FinstatApi', $dom->textContent, $response->status_code);

                default:
                    throw new Requests_Exception('Unknown exception while communication with Finstat api!', 'FinstatApi', $dom->textContent, $response->status_code);
            }
        }

        $detail = false;
        if($json) {
            $detail = json_decode($response->body);
        } else {
            $detail = simplexml_load_string($response->body);
        }

        if($detail === FALSE)
            throw new Requests_Exception('Error while parsing XML data.', 'FinstatApi');

        return $detail;
    }

    public function GetAPILimits()
    {
        if(empty($this->limits))
        {
            throw new  Exception('Limits are available after API call');
        }

        return $this->limits;
    }

    public function RemoveFromMonitoring($ico, $json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'ico' => $ico,
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash($ico),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "RemoveFromMonitoring";
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
        $parse = (string)$detail;

         return ($json) ? $detail : ($parse == 'true');
    }

    public function RemoveDateFromMonitoring($date, $json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'date' => $date,
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash($date),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "RemoveDateFromMonitoring";
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

        $detail = $this->parseResponse($response, $url, $date, $json);
        $parse = (string)$detail;

         return ($json) ? $detail : ($parse == 'true');
    }

    public function MonitoringList($json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash('list'),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "MonitoringList";
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

        return ($json) ? $detail : $this->parseMonitoringList($detail);
    }

    public function MonitoringDateList($json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash('datelist'),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "MonitoringDateList";
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

        return ($json) ? $detail : $this->parseMonitoringList($detail);
    }

    public function MonitoringReport($json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash('report'),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "MonitoringReport";
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

        return ($json) ? $detail : $this->parseMonitoringReport($detail);
    }

    public function MonitoringDateReport($json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash('datereport'),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "MonitoringDateReport";
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
        return ($json) ? $detail : $this->parseMonitoringDateReport($detail);
    }

    public function MonitoringProceedings($json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash('proceedings'),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "MonitoringProceedings";
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
        return ($json) ? $detail : $this->parseMonitoringProceedings($detail);
    }

    public function MonitoringDateProceedings($json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash('dateproceedings'),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "MonitoringDateProceedings";
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
        return ($json) ? $detail : $this->parseMonitoringProceedings($detail);
    }

    private function parseMonitoringList($detail)
    {
        if  ($detail === FALSE) {
            return $detail;
        }

        $response =  array();
        if (!empty($detail->string)) {
            foreach ($detail->string as $s) {
                $response[] = (string) $s;
            }
        }

        return $response;
    }

    private function parseMonitoringReport($detail)
    {
        if  ($detail === FALSE) {
            return $detail;
        }

        $response =  array();
        if (!empty($detail->Monitoring)) {
            foreach ($detail->Monitoring as $element) {
                $o = new MonitoringReportResult();
                $o->Ident        = (string)$element->Ident;
                $o->Ico          = (string)$element->Ico;
                $o->Name         = (string)$element->Name;
                $o->PublishDate  = empty($element->PublishDate) ? null : new DateTime($element->PublishDate);
                $o->Type         = (string)$element->Type;
                $o->Description  = (string)$element->Description;
                $o->Url          = (string)$element->Url;
                $response[] = $o;
            }
        }

        return $response;
    }

    private function parseMonitoringDateReport($detail)
    {
        if  ($detail === FALSE) {
            return $detail;
        }

        $response =  array();
        if (!empty($detail->MonitoringDate)) {
            foreach ($detail->MonitoringDate as $element) {
                $o = new MonitoringDateReportResult();
                $o->Ident        = (string)$element->Ident;
                $o->Date         = (string)$element->Date;
                $o->Name         = (string)$element->Name;
                $o->PublishDate  = empty($element->PublishDate) ? null : new DateTime($element->PublishDate);
                $o->Type         = (string)$element->Type;
                $o->Description  = (string)$element->Description;
                $o->Url          = (string)$element->Url;
                $response[] = $o;
            }
        }

        return $response;
    }

    private function parseAddress($element, $object = null)
    {
        $o = ($object != null) ? $object : new Address();
        $o->Name            = (string)$element->Name;
        $o->Street          = (string)$element->Street;
        $o->StreetNumber    = (string)$element->StreetNumber;
        $o->ZipCode         = (string)$element->ZipCode;
        $o->City            = (string)$element->City;
        $o->Country         = (string)$element->Country;
        $o->Region          = (string)$element->Region;
        $o->District        = (string)$element->District;
        return $o;
    }

    private function parsePersonAddress($element)
    {
        $o = $this->parseAddress($element, new PersonAddress());
        $o->Ico         = (string)$element->Ico;
        $o->BirthDate   = (string)$element->BirthDate;
        return $o;
    }

    private function parseMonitoringProceedings($detail)
    {
        if  ($detail === FALSE) {
            return $detail;
        }

        $response =  array();
        if (!empty($detail->ProceedingResult)) {
            foreach ($detail->ProceedingResult as $element) {
                $o = new ProceedingResult();
                if(!empty($element->DebtorsAddress)) {
                    $array = array();
                    foreach ($element->DebtorsAddress->PersonAddress as $address) {
                        $array[] = $this->parsePersonAddress($address);
                    }
                    $o->DebtorsAddress = $array;
                }
                if(!empty($element->ProposersAddress)) {
                    $array = array();
                    foreach ($element->ProposersAddress->PersonAddress as $address) {
                        $array[] = $this->parsePersonAddress($address);
                    }
                    $o->ProposersAddress = $array;
                }
                if(!empty($element->AdministratorsAddress)) {
                    $array = array();
                    foreach ($element->AdministratorsAddress->PersonAddress as $address) {
                         $array[] = $this->parsePersonAddress($address);
                    }
                    $o->AdministratorsAddress = $array;
                }
                if(!empty($element->CourtsAddress)) {
                    $o->CourtsAddress  = $this->parseAddress($element->CourtsAddress);
                }
                $o->ReferenceFileNumber     = (string)$element->ReferenceFileNumber;
                $o->Status                  = (string)$element->Status;
                $o->Character               = (string)$element->Character;
                $o->EndReason               = (string)$element->EndReason;
                $o->EndStatus               = (string)$element->EndStatus;
                $o->Url                     = (string)$element->Url;
                $o->Type                    = (string)$element->Type;
                $o->PublishDate             = empty($element->PublishDate) ? null : new DateTime($element->PublishDate);
                $o->Deadline                = empty($element->Deadline) ? null : new DateTime($element->Deadline);
                $response[] = $o;
            }
        }

        return $response;
    }

    //
    // Compute verification hash
    //
    private function ComputeVerificationHash($parameter)
    {
        $data = sprintf("SomeSalt+%s+%s++%s+ended", $this->apiKey, $this->privateKey, $parameter);

        return hash('sha256', $data);
    }
}
?>