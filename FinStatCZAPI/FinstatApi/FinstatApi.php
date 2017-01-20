<?php

require_once('Requests.php');
require_once('DetailResult.php');
require_once('AutoCompleteResult.php');

class FinstatApi
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
        $this->privateKey = $privateKey;
        $this->stationId = $stationId;
        $this->stationName = $stationName;
        $this->timeout = $timeout;
        $this->limits = null;
    }

    public function RequestAutoComplete($query, $json = false)
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
            'query' => $query,
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash($query),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "autocomplete";

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

        $detail = $this->parseResponse($response, $url, $query, $json);
        if(!$json) {
            return $this->parseAutoComplete($detail);
        } else {
            return $detail;
        }
    }

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

    private function parseResponse($response, $url, $parameter, $json = false)
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

    private function parseAddress($detail, $response)
    {
        $response->Street           = (string)$detail->Street;
        $response->StreetNumber     = (string)$detail->StreetNumber;
        $response->ZipCode          = (string)$detail->ZipCode;
        $response->City             = (string)$detail->City;
        $response->District         = (string)$detail->District;
        $response->Region           = (string)$detail->Region;
        $response->Country          = (string)$detail->Country;

        return $response;
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

    private function parseAutoComplete($detail)
    {
        if  ($detail === FALSE) {
            return $detail;
        }

        $response = new AutoCompleteResult();
        $response->Results = array();
        if (!empty($detail->Results) && !empty($detail->Results->Company)) {
            foreach($detail->Results->Company as $company) {
                $oc = new CompanyResult();
                $oc->Ico  = (string) $company->Ico;
                $oc->Name  = (string) $company->Name;
                $oc->City  = (string) $company->City;
                $oc->Cancelled = (((string)$company->Cancelled) == "true");
                $response->Results[] = $oc;
            }
        }
        $response->Suggestions = array();
        if (!empty($detail->Suggestions) && !empty($detail->Suggestions->string)) {
            foreach($detail->Suggestions->string as $string) {
                $response->Suggestions[] = (string)$string;
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

    /**
     * Parses date string received from API and returns DateTime object or null.
     *
     * @param SimpleXMLElement $date
     * @return DateTime|null
     */
    private function parseDate(SimpleXMLElement $date) {

        if (!((string) $date)) {
          return null;
        }

        return new DateTime((string)$date);
    }

}
