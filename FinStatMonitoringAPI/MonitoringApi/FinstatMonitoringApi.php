<?php

require_once('Requests.php');
require_once('MonitoringReportResult.php');

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

    public function AddToMonitoring($ico)
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
            $response = Requests::post($url, null, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }

        $detail = $this->parseResponse($response, $url, $ico);
        $parse = (string)$detail;

        return ($parse == 'true');
    }

    private function parseResponse($response, $url, $parameter = null)
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

        $detail = simplexml_load_string($response->body);

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

    public function RemoveFromMonitoring($ico)
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
            $response = Requests::post($url, null, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }

        $detail = $this->parseResponse($response, $url, $ico);
        $parse = (string)$detail;

        return ($parse == 'true');
    }

    public function MonitoringList()
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
            $response = Requests::post($url, null, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }

        $detail = $this->parseResponse($response, $url);

        return $this->parseMonitoringList($detail);
    }

    public function MonitoringReport()
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
            $response = Requests::post($url, null, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }

        $detail = $this->parseResponse($response, $url);

        return $this->parseMonitoringReport($detail);
    }

    public function RequestZRSRScan($ico, $email = null)
    {
        $options = $this->InitRequests();

        $data = array(
            'ico' => $ico,
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash('requestzrsr'),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        if(isset($email) && !empty($email)) {
            $data["email"] = $email;
        }

        $url = $this->apiUrl . "RequestZRSRScan";
        try
        {
            $response = Requests::post($url, null, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }

        $detail = $this->parseResponse($response, $url, $ico);
        $parse = (string)$detail;

        return ($parse == 'true');
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