<?php

require_once('Requests.php');
require_once('DetailResult.php');
require_once('ExtendedResult.php');
require_once('UltimateResult.php');
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

    public function RequestExtended($ico)
    {
        return $this->Request($ico, 'extended');
    }

    public function RequestUltimate($ico)
    {
        return $this->Request($ico, 'ultimate');
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
            case 'ultimate':
                $detail = $this->parseUltimate($detail);
                break;
            case 'extended':
                $detail = $this->parseExtended($detail);
                break;
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
        $response->RegisterNumberText   = "{$detail->RegisterNumberText}";
        $response->Dic                  = "{$detail->Dic}";
        $response->IcDPH                = "{$detail->IcDPH}";
        $response->Name                 = "{$detail->Name}";
        $response->Street               = "{$detail->Street}";
        $response->StreetNumber         = "{$detail->StreetNumber}";
        $response->ZipCode              = "{$detail->ZipCode}";
        $response->City                 = "{$detail->City}";
        $response->Created              = $this->parseDate($detail->Created);
        $response->Cancelled            = $this->parseDate($detail->Cancelled);
        $response->SuspendedAsPerson    = "{$detail->SuspendedAsPerson}"  == 'true' ;
        $response->Activity             = "{$detail->Activity}";
        $response->Url                  = "{$detail->Url}";
        $response->Warning              = "{$detail->Warning}"  == 'true' ;
        $response->WarningUrl           = "{$detail->WarningUrl}";
        $response->PaymentOrderWarning  = "{$detail->PaymentOrderWarning}"  == 'true';
        $response->PaymentOrderUrl      = "{$detail->PaymentOrderUrl}";
        $response->OrChange             = "{$detail->OrChange}"  == 'true';
        $response->OrChangeUrl          = "{$detail->OrChangeURL}";
        $response->Revenue              = "{$detail->Revenue}";
        $response->Profit               = "{$detail->Profit}";

        return $response;
    }

    private function parseExtended($detail, $response = null)
    {
        if  ($detail === FALSE) {
            return $detail;
        }

        $response = ($response == null)? new ExtendedResult() : $response;
        $response->Ico                  = "{$detail->Ico}";
        $response->RegisterNumberText   = "{$detail->RegisterNumberText}";
        $response->Dic                  = "{$detail->Dic}";
        $response->IcDPH                = "{$detail->IcDPH}";
        $response->Name                 = "{$detail->Name}";
        $response->Street               = "{$detail->Street}";
        $response->StreetNumber         = "{$detail->StreetNumber}";
        $response->ZipCode              = "{$detail->ZipCode}";
        $response->City                 = "{$detail->City}";
        $response->District             = "{$detail->District}";
        $response->Region               = "{$detail->Region}";
        $response->Created              = $this->parseDate($detail->Created);
        $response->Cancelled            = $this->parseDate($detail->Cancelled);
        $response->Activity             = "{$detail->Activity}";
        $response->Url                  = "{$detail->Url}";
        $response->Warning              = "{$detail->Warning}"  == 'true' ;
        $response->WarningUrl           = "{$detail->WarningUrl}";
        $response->PaymentOrderWarning  = "{$detail->PaymentOrderWarning}"  == 'true';
        $response->OrChange             = "{$detail->OrChange}"  == 'true';
        $response->OrChangeUrl          = "{$detail->OrChangeURL}";
        $response->SkNaceCode           = "{$detail->SkNaceCode}";
        $response->SkNaceText           = "{$detail->SkNaceText}";
        $response->SkNaceDivision       = "{$detail->SkNaceDivision}";
        $response->SkNaceGroup          = "{$detail->SkNaceGroup}";
        $response->EmployeeCode         = "{$detail->EmployeeCode}";
        $response->EmployeeText         = "{$detail->EmployeeText}";
        $response->LegalFormCode        = (string)$detail->LegalFormCode;
        $response->LegalFormText        = (string)$detail->LegalFormText;
        $response->OwnershipTypeCode    = (string)$detail->OwnershipTypeCode;
        $response->OwnershipTypeText    = (string)$detail->OwnershipTypeText;
        $response->ActualYear           = (int)"{$detail->ActualYear}";
        $response->CreditScoreValue     = (float)$detail->CreditScoreValue;
        $response->CreditScoreState     = (string)$detail->CreditScoreState;
        $response->RevenueActual        = empty($detail->RevenueActual) ? null :(double)"{$detail->RevenueActual}";
        $response->RevenuePrev          = empty($detail->RevenuePrev) ? null :(double)"{$detail->RevenuePrev}";
        $response->ProfitActual         = empty($detail->ProfitActual) ? null :(double)"{$detail->ProfitActual}";
        $response->ProfitPrev           = empty($detail->ProfitPrev) ? null :(double)"{$detail->ProfitPrev}";
        $response->ForeignResources     = empty($detail->ForeignResources) ? null :(double)"{$detail->ForeignResources}";
        $response->GrossMargin          = empty($detail->GrossMargin) ? null :(double)"{$detail->GrossMargin}";
        $response->ROA                  = empty($detail->ROA) ? null : (double)"{$detail->ROA}";
        $response->WarningKaR           = $this->parseDate($detail->WarningKaR);
        $response->WarningLiquidation   = $this->parseDate($detail->WarningLiquidation);
        $response->SelfEmployed         = "{$detail->SelfEmployed}"  == 'true';
        $response->Phones = array();
        if (!empty($detail->Phones)) {
            foreach ($detail->Phones->string as $s) {
                $response->Phones[] = "{$s}";
            }
        }
        $response->Emails = array();
        if (!empty($detail->Emails)) {
            foreach ($detail->Emails->string as $s) {
                $response->Emails[] = "{$s}";
            }
        }
        $response->Debts = array();

        if (!empty($detail->Debts)) {
            foreach ($detail->Debts->Debt as $debt) {
                $o = new DebtResult();
                $o->Source  = $debt->Source;
                $o->Value   = (double)$debt->Value;
                $o->ValidFrom  = $this->parseDate($detail->ValidFrom);
                $response->Debts[] = $o;
            }
        }

        $response->PaymentOrders = array();

        if (!empty($detail->PaymentOrders)) {
            foreach ($detail->PaymentOrders->PaymentOrder as $paymentOrder) {
                $o = new PaymentOrderResult();
                $o->PublishDate  = $this->parseDate($paymentOrder->PublishDate);
                $o->Value   = (double)$paymentOrder->Value;
                $response->PaymentOrders[] = $o;
            }
        }

        if (!empty($detail->IcDphAdditional)) {
            $response->IcDphAdditional = $this->parseIcDphAdditional($detail->IcDphAdditional);
        }
        
        if (!empty($detail->Offices)) {
            $response->Offices = array();
            foreach ($detail->Offices->Office as $office) {
                $o = new OfficeResult();
                $o->City = (string)$office->City;
                $o->Country = (string)$office->Country;
                $o->District = (string)$office->District;
                $o->Region = (string)$office->Region;
                $o->Street = (string)$office->Street;
                $o->StreetNumber = (string)$office->StreetNumber;
                $o->ZipCode = (string)$office->ZipCode;
                $o->Type = (string)$office->Type;
                if(!empty($office->Subjects)) {
                     $o->Subjects = array();
                     foreach ($office->Subjects->string as $s) {
                        $o->Subjects[] = (string)$s;
                    }
                }
                $response->Offices[] = $o;
            }
        }

        return $response;
    }

    private function parseUltimate($detail)
    {
        if ($detail === FALSE) {
            return $detail;
        }

        $response = $this->parseExtended($detail, new UltimateResult());
        if ($response !== FALSE) {
            $response->ORSection = (string)$detail->ORSection;
            $response->ORInsertNo = (string)$detail->ORInsertNo;
            $response->Persons = array();
            if (!empty($detail->Persons)) {
                foreach ($detail->Persons->Person as $person) {
                    $o = new PersonResult();
                    $o->FullName = (string)$person->FullName;
                    $o->Street = (string)$person->Street;
                    $o->StreetNumber = (string)$person->StreetNumber;
                    $o->ZipCode = (string)$person->ZipCode;
                    $o->City = (string)$person->City;
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
                    $response->Persons[] = $o;
                }
            }
        }

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
