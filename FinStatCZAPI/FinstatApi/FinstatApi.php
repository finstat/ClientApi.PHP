<?php

require_once('Requests.php');
require_once('BaseResultCZ.php');
require_once('AutoCompleteResult.php');

class FinstatApi
{
    private
        $apiUrl,
        $apiKey,
        $privateKey,
        $stationId,
        $stationName,
        $timeout;

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
    }

    public function RequestAutoComplete($query)
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
            $response = Requests::post($url, null, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }

        if(!$response->success)
        {
            switch($response->status_code)
            {
                case 404:
                    throw new Requests_Exception("Not valid URL: '$url' or specified ico: '$ico' not found in database!", 'FinstatApi', null, $response->status_code);

                case 403:
                    throw new Requests_Exception('Not valid API key!', 'FinstatApi', null, $response->status_code);

                default:
                    throw new Requests_Exception('Unknown exception while communication with Finstat api!', 'FinstatApi', null, $response->status_code);
            }
        }

        $detail = simplexml_load_string($response->body);
        if($detail === FALSE)
            throw new Requests_Exception('Error while parsing XML data.', 'FinstatApi');

        return $this->parseAutoComplete($detail);
    }

    public function RequestDetail($ico)
    {
        return $this->Request($ico);
    }

    //
    // Requests the detail for specified ico
    //
    // Returns: details or FALSE
    public function Request($ico, $type="detail")
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
            $response = Requests::post($url, null, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }

        if(!$response->success)
        {
            switch($response->status_code)
            {
                case 404:
                    throw new Requests_Exception("Not valid URL: '$url' or specified ico: '$ico' not found in database!", 'FinstatApi', null, $response->status_code);

                case 403:
                    throw new Requests_Exception('Not valid API key!', 'FinstatApi', null, $response->status_code);

                default:
                    throw new Requests_Exception('Unknown exception while communication with Finstat api!', 'FinstatApi', null, $response->status_code);
            }
        }

        $detail = simplexml_load_string($response->body);
        if($detail === FALSE)
            throw new Requests_Exception('Error while parsing XML data.', 'FinstatApi');

        switch($type) {
            case 'detail':
            default:
                $detail = $this->parseDetail($detail);
                break;
        }

        return $detail;
    }

    private function parseDetail($detail)
    {
        if  ($detail === FALSE) {
            return $detail;
        }

        $response = new DetailResult();
        $response->Ico                  = "{$detail->Ico}";
        $response->CZNACE               = "{$detail->CZNACE}";
        $response->Name                 = "{$detail->Name}";
        $response->Street               = "{$detail->Street}";
        $response->StreetNumber         = "{$detail->StreetNumber}";
        $response->ZipCode              = "{$detail->ZipCode}";
        $response->City                 = "{$detail->City}";
        $response->Region               = "{$detail->Region}";
        $response->District             = "{$detail->District}";
        $response->Created              = $this->parseDate($detail->Created);
        $response->Cancelled            = $this->parseDate($detail->Cancelled);
        $response->Activity             = "{$detail->Activity}";
        $response->Url                  = "{$detail->Url}";
        $response->Warning              = "{$detail->Warning}"  == 'true' ;
        $response->WarningUrl           = "{$detail->WarningUrl}";
        $response->LegalForm            = "{$detail->LegalForm}";
        $response->OwnershipType        = "{$detail->OwnershipType}";
        $response->EmployeeCount        = "{$detail->EmployeeCount}";

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
    private function ComputeVerificationHash($ico)
    {
        $data = sprintf("SomeSalt+%s+%s++%s+ended", $this->apiKey, $this->privateKey, $ico);

        return hash('sha256', $data);
    }

    private function parseIcDphAdditional(SimpleXMLElement $icDphAdditional) {
        $result = new IcDphAdditionalResult();
        $result->IcDph = (string) $icDphAdditional->IcDph;
        $result->Paragraph = (string) $icDphAdditional->Paragraph;
        $result->CancelListDetectedDate = $this->parseDate($icDphAdditional->CancelListDetectedDate);
        $result->RemoveListDetectedDate = $this->parseDate($icDphAdditional->RemoveListDetectedDate);

        return $result;
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

        return new DateTime($date);
    }

}
