<?php
require_once(__DIR__ . '/Requests.php');
require_once(__DIR__ . '/ViewModel/AutoCompleteResult.php');

class AbstractFinstatApi
{
    protected
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
        $this->privateKey = $privateKey;
        $this->stationId = $stationId;
        $this->stationName = $stationName;
        $this->timeout = $timeout;
        $this->limits = null;
    }

    public function InitRequests()
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

    public function DoBaseRequest($requestUrl, $requestData, $parameter = null, $json = false) {
        $options = $this->InitRequests();

        $data = array_merge(array(
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash($parameter),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        ), $requestData);
        
        $url = $this->apiUrl. $requestUrl;
        try
        {
            $headers = null;
            if ($json) {
                $url = $url . ".json";
            }


            return Requests::post($url, $headers, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }
    }

    public function DoRequest($requestUrl, $requestData, $parameter = null, $json = false)
    {
        try
        {
            $url = $this->apiUrl. $requestUrl;
            $response = $this->DoBaseRequest($requestUrl, $requestData, $parameter, $json);
            return $this->parseResponse($response, $url, $parameter, $json);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }
    }

    //
    // Compute verification hash
    //
    protected function ComputeVerificationHash($parameter)
    {
        $data = sprintf("SomeSalt+%s+%s++%s+ended", $this->apiKey, $this->privateKey, $parameter);

        return hash('sha256', $data);
    }

    protected function parseResponseRaw($response, $url, $parameter, $json = false)
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
    }

    protected function parseResponse($response, $url, $parameter, $json = false)
    {
        $this->parseResponseRaw($response, $url, $parameter, $json);
        $detail = false;
        if($json)
        {
            $detail = json_decode($response->body);
        }
        else
        {
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

    protected function parseAddress($detail, $response)
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

    protected function parseFullAddress($element, $object = null)
    {
        $o = ($object != null) ? $object : new Address();
        $o = $this->parseAddress($element, $o);
        $o->Name            = (string)$element->Name;
        return $o;
    }

    protected function parsePersonAddress($element, $object = null)
    {
        $o = ($object != null) ? $object : new PersonAddress();
        $o = $this->parseFullAddress($element, $o);
        $o->Ico         = (string)$element->Ico;
        $o->BirthDate   = (string)$element->BirthDate;
        return $o;
    }

    protected function parseStructuredName($element)
    {
        $o = new NamePartsResult();
        if(!empty($element->Prefix)) {
            $o->Prefix = array();
            foreach ($element->Prefix->string as $s) {
                $o->Prefix[] = (string)$s;
            }
        }
        if(!empty($element->Name)) {
            $o->Name = array();
            foreach ($element->Name->string as $s) {
               $o->Name[] = (string)$s;
            }
        }
        if(!empty($element->Suffix)) {
            $o->Suffix = array();
            foreach ($element->Suffix->string as $s) {
               $o->Suffix[] = (string)$s;
            }
        }
        if(!empty($element->After)) {
            $o->After = array();
            foreach ($element->After->string as $s) {
               $o->After[] = (string)$s;
            }
        }
        return $o;
    }

    protected function parsePerson($person, $o = null)
    {
        $o = ($o == null) ? new PersonResult() : $o;
        $o = $this->parseAddress($person, $o);
        $o->FullName = (string)$person->FullName;
        $o->DetectedFrom = $this->parseDate($person->DetectedFrom);
        $o->DetectedTo  = $this->parseDate($person->DetectedTo);
        $o->Functions = array();
        if (!empty($person->Functions) && !empty($person->Functions->FunctionAssigment)) {
            foreach($person->Functions->FunctionAssigment as $function) {
                $of = new FunctionResult();
                $of->Type = (string)$function->Type;
                $of->Description = (string)$function->Description;
                $of->From = $this->parseDate($function->From);
                $o->Functions[] = $of;
            }
        }
        if(!empty($person->StructuredName)) {
            $o->StructuredName = $this->parseStructuredName($person->StructuredName);
        }

        return $o;
    }

    /**
     * Parses date string received from API and returns DateTime object or null.
     *
     * @param SimpleXMLElement $date
     * @return DateTime|null
     */
    protected function parseDate(SimpleXMLElement $date = null) {

        if (empty($date) || !((string) $date)) {
          return null;
        }

        return new DateTime($date);
    }

    protected function parseIcDphAdditional(SimpleXMLElement $icDphAdditional) {
        $result = new IcDphAdditionalResult();
        $result->IcDph = (string) $icDphAdditional->IcDph;
        $result->Paragraph = (string) $icDphAdditional->Paragraph;
        $result->CancelListDetectedDate = $this->parseDate($icDphAdditional->CancelListDetectedDate);
        $result->RemoveListDetectedDate = $this->parseDate($icDphAdditional->RemoveListDetectedDate);

        return $result;
    }
}
