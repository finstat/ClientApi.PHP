<?php

namespace FinStat\Client;

use FinStat\Client\ViewModel\AddressResult;
use FinStat\Client\ViewModel\PersonAddressResult;
use FinStat\Client\ViewModel\AutoCompleteResult;
use FinStat\Client\ViewModel\CompanyResult;
use FinStat\Client\ViewModel\Detail\AbstractResult;
use FinStat\Client\ViewModel\Detail\CommonResult;
use DateTime;
use SimpleXMLElement;
use DOMDocument;

class BaseFinstatApi extends AbstractFinstatApi
{
    public function RequestAutoLogin($redirecturl, $email = null, $json = false)
    {
        $data = array('url' => $redirecturl);
        if (!empty($email)) {
            $data['email'] = $email;
        }

        $detail = $this->DoRequest("autologin", $data, "autologin", $json);

        return (string)$detail;
    }

    public function RequestAutoComplete($query, $json = false)
    {
        $detail = $this->DoRequest("autocomplete", array('query' => $query), $query, $json);

        if(!$json) {
            return $this->parseAutoComplete($detail);
        } else {
            return $detail;
        }
    }

    protected function parseAutoComplete($detail)
    {
        if  ($detail === false) {
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
        $response->Suggestions = $this->parseStringArray($detail->Suggestions);

        return $response;
    }

    protected function parseAbstractResult($detail, $response = null)
    {
        if  ($detail === false) {
            return $detail;
        }
        $response = ($response == null) ? new AbstractResult() : $response;
        $response = $this->parseFullAddress($detail, $response);
        $response->Ico                  = (string)$detail->Ico;
        $response->Url                  = (string)$detail->Url;
        $response->Created              = $this->parseDate($detail->Created);
        $response->Cancelled            = $this->parseDate($detail->Cancelled);
        $response->SuspendedAsPerson    = "{$detail->SuspendedAsPerson}" == 'true';

        return $response;
    }

    protected function parseCommonResult($detail, $response = null)
    {
        if  ($detail === false) {
            return $detail;
        }
        $response = ($response == null) ? new CommonResult() : $response;
        $response = $this->parseAbstractResult($detail, $response);
        $response->IcDPH                = (string)$detail->IcDPH;
        $response->Dic                  = (string)$detail->Dic;
        $response->Activity             = (string)$detail->Activity;
        $response->Warning              = "{$detail->Warning}"  == 'true' ;
        $response->WarningUrl           = (string)$detail->WarningUrl;

        return $response;
    }

    /**
     * Parse XML string array element into PHP array
     * Handles SimpleXMLElement objects containing <string> child elements
     * 
     * @param SimpleXMLElement|null $element The XML element containing string children
     * @return array Array of strings, or empty array if element is empty
     */
    protected function parseStringArray($element): array
    {
        if (empty($element) || empty($element->string)) {
            return array();
        }
        
        $result = array();
        foreach ($element->string as $s) {
            $result[] = (string)$s;
        }
        return $result;
    }

    /**
     * Parse XML object array element into PHP array using a parser callback
     * Generic method for parsing collections of complex objects
     * 
     * @param SimpleXMLElement|null $element The XML element containing object children
     * @param string $itemElementName Name of the child element (e.g., 'Debt', 'Company')
     * @param callable $parser Callback function to parse each item: function($item) { return $parsed; }
     * @return array Array of parsed objects, or empty array if element is empty
     */
    protected function parseObjectArray($element, string $itemElementName, callable $parser): array
    {
        if (empty($element)) {
            return array();
        }
        
        $result = array();
        foreach ($element->{$itemElementName} as $item) {
            $result[] = $parser($item);
        }
        return $result;
    }

    /**
     * Parse boolean value from XML element
     * 
     * @param SimpleXMLElement|null $element The XML element
     * @return bool True if element equals 'true', false otherwise
     */
    protected function parseBoolean($element): bool
    {
        return !empty($element) && (string)$element === 'true';
    }

    /**
     * Parse nullable string from XML element
     * 
     * @param SimpleXMLElement|null $element The XML element
     * @return string|null String value or null if empty
     */
    protected function parseNullableString($element): ?string
    {
        return !empty($element) ? (string)$element : null;
    }
}
