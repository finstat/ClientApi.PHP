<?php

namespace FinStat\Client;

use DateTime;
use DOMDocument;
use SimpleXMLElement;
use Exception;
use RuntimeException;
use FinStat\Client\Exceptions\FinstatException;
use FinStat\Client\Exceptions\NotFoundException;
use FinStat\Client\Exceptions\LimitReachedException;
use FinStat\Client\Exceptions\AuthenticationException;
use FinStat\Client\Exceptions\AccessDisabledException;
use FinStat\Client\Exceptions\BadRequestException;
use FinStat\Client\Exceptions\GdprRestrictionException;
use FinStat\Client\Exceptions\InsufficientAccessException;
use FinStat\Client\Exceptions\InvalidHashException;
use FinStat\Client\Exceptions\LicenseExpiredException;
use FinStat\Client\Exceptions\ParseException;
use FinStat\Client\Exceptions\UnauthorizedException;
use FinStat\ViewModel\Detail\FunctionResult;
use FinStat\ViewModel\Detail\IcDphAdditionalResult;
use FinStat\ViewModel\Detail\NamePartsResult;
use FinStat\ViewModel\Detail\PersonResult;

class AbstractFinstatApi
{
    protected $apiUrl;
    protected $apiKey;
    protected $privateKey;
    protected $stationId;
    protected $stationName;
    protected $timeout;
    protected $limits;

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

    /**
     * Initialize HTTP client options
     * 
     * @return array Options array for HTTP client
     */
    public function InitRequests()
    {
        return array(
            'timeout' => $this->timeout,
            'follow_redirects' => false,
            'auth' => false
        );
    }

    /**
     * Make HTTP request to API
     * 
     * @param string $requestUrl API endpoint
     * @param array $requestData Request data
     * @param string|null $parameter Request parameter
     * @param bool $json Whether to request JSON response
     * @return HttpResponse Response object
     * @throws RuntimeException On HTTP error
     */
    public function DoBaseRequest($requestUrl, $requestData, $parameter = null, $json = false)
    {
        $options = $this->InitRequests();

        $data = array_merge(array(
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash($parameter),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        ), $requestData);
        
        $url = $this->apiUrl. $requestUrl;
        if ($json) {
            $url = $url . ".json";
        }

        try {
            return HttpClient::post($url, null, $data, $options);
        } catch (RuntimeException $e) {
            throw $e;
        }
    }

    /**
     * Make API request and parse response
     * 
     * @param string $requestUrl API endpoint
     * @param array $requestData Request data
     * @param string|null $parameter Request parameter
     * @param bool $json Whether to request JSON response
     * @return mixed Parsed response
     * @throws FinstatException On API error
     */
    public function DoRequest($requestUrl, $requestData, $parameter = null, $json = false)
    {
        try {
            $url = $this->apiUrl. $requestUrl;
            $response = $this->DoBaseRequest($requestUrl, $requestData, $parameter, $json);
            return $this->parseResponse($response, $url, $parameter, $json);
        } catch (RuntimeException $e) {
            throw new FinstatException('HTTP request failed: ' . $e->getMessage(), 0, $e);
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

        if(!$response->success) {
            $dom = new DOMDocument();
            $dom->loadHTML($response->body);
            $body = (string)$response->body;

            switch($response->status_code) {
                case 404:
                    $exception = new NotFoundException($parameter, $response->status_code);
                    $exception->setRequestContext($url, $parameter);
                    throw $exception;

                case 402:
                    $exception = new LimitReachedException(
                        (int)($this->limits['daily']['current'] ?? 0),
                        (int)($this->limits['daily']['max'] ?? 0),
                        (int)($this->limits['monthly']['current'] ?? 0),
                        (int)($this->limits['monthly']['max'] ?? 0),
                        $response->status_code
                    );
                    $exception->setRequestContext($url, $parameter);
                    throw $exception;

                case 401:
                    $exception = new UnauthorizedException(
                        !empty($body) ? $body : 'Unauthorized.',
                        $response->status_code
                    );
                    $exception->setRequestContext($url, $parameter);
                    throw $exception;

                case 403:
                    // Discriminate the generic 403 into its semantic sub-types by inspecting
                    // the response body — server messages are stable strings emitted from
                    // finstat.Controllers.ApiFinstatController.CheckAccess.
                    if (strpos($body, 'Insufficient access') !== false) {
                        $exception = new InsufficientAccessException($body, $response->status_code);
                    } elseif (strpos($body, 'Your API access and FinStat license expired') !== false) {
                        $exception = new LicenseExpiredException($body, $response->status_code);
                    } elseif (strpos($body, 'Your API access is disabled') !== false) {
                        $exception = new AccessDisabledException($body, $response->status_code);
                    } elseif (strpos($body, 'Invalid verification hash') !== false) {
                        $exception = new InvalidHashException($body, $response->status_code);
                    } else {
                        $exception = new AuthenticationException(
                            !empty($body) ? $body : 'Access Forbidden. Check API credentials.',
                            $response->status_code
                        );
                    }
                    $exception->setRequestContext($url, $parameter);
                    throw $exception;

                case 400:
                    $exception = new BadRequestException(
                        !empty($body) ? $body : 'Bad Request. Invalid parameters.',
                        $response->status_code
                    );
                    $exception->setRequestContext($url, $parameter);
                    throw $exception;

                case 451:
                    $exception = new GdprRestrictionException(
                        !empty($body) ? $body : 'Limited access due to GDPR restrictions.',
                        $response->status_code
                    );
                    $exception->setRequestContext($url, $parameter);
                    throw $exception;

                default:
                    $message = 'HTTP ' . $response->status_code . ': ' . $dom->textContent;
                    $exception = new FinstatException($message, $response->status_code);
                    $exception->setRequestContext($url, $parameter);
                    throw $exception;
            }
        }
    }

    protected function parseResponse($response, $url, $parameter, $json = false)
    {
        $this->parseResponseRaw($response, $url, $parameter, $json);
        
        try {
            if($json) {
                $detail = json_decode($response->body);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new ParseException('Error while parsing JSON data: ' . json_last_error_msg());
                }
            } else {
                $detail = simplexml_load_string($response->body);
                if($detail === false) {
                    throw new ParseException('Error while parsing XML data.');
                }
            }
            
            return $detail;
        } catch (Exception $e) {
            if ($e instanceof ParseException) {
                throw $e;
            }
            throw new ParseException('Error parsing response: ' . $e->getMessage(), 0, $e);
        }
    }

    public function GetAPILimits()
    {
        if(empty($this->limits)) {
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
        $o = ($object != null) ? $object : new PersonAddressResult();
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

        if(!empty($person->BirthDate)) {
            $o->BirthDate = (!empty($person->BirthDate)) ? $this->parseDate($person->BirthDate) : null;
        }

        return $o;
    }

    /**
     * Parse date string received from API and returns DateTime object or null.
     *
     * @param SimpleXMLElement $date
     * @return DateTime|null
     */
    protected function parseDate(SimpleXMLElement|null $date = null)
    {
        if (empty($date) || !((string) $date)) {
            return null;
        }

        return new DateTime($date);
    }

    protected function parseIcDphAdditional(SimpleXMLElement $icDphAdditional)
    {
        $result = new IcDphAdditionalResult();
        $result->IcDph = (string) $icDphAdditional->IcDph;
        $result->Paragraph = (string) $icDphAdditional->Paragraph;
        $result->CancelListDetectedDate = $this->parseDate($icDphAdditional->CancelListDetectedDate);
        $result->RemoveListDetectedDate = $this->parseDate($icDphAdditional->RemoveListDetectedDate);

        return $result;
    }

    protected function DownloadFile($requestUrl, $fileName, $exportPath)
    {
        $response = $this->DoBaseRequest($requestUrl, array('fileName' => $fileName), $fileName, false);

        $url = $this->apiUrl. $requestUrl;
        $this->parseResponseRaw($response, $url, $fileName);

        return file_put_contents($exportPath, $response->body);
    }
}
