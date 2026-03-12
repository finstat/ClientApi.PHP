<?php
namespace FinStatCZ;

use FinStat\Client\BaseFinstatApi;
use FinStat\Client\ViewModel\Detail\BankAccount;
use FinStatCZ\ViewModel\Detail\BasicResult;
use FinStatCZ\ViewModel\Detail\DetailResult;
use FinStatCZ\ViewModel\Detail\PremiumCZResult;
use FinStatCZ\ViewModel\Detail\EliteCZResult;
use FinStatCZ\ViewModel\Detail\IsirResult;
use FinStatCZ\ViewModel\Detail\IndicatorResult;
use FinStatCZ\ViewModel\Detail\IndicatorValue;


class FinstatApi extends BaseFinstatApi
{
    public function RequestBasic($ico, $json = false)
    {
        return $this->Request($ico, 'basic', $json);
    }

    public function RequestDetail($ico, $json = false)
    {
        return $this->Request($ico, 'detail', $json);
    }

    public function RequestPremiumCZ($ico, $json = false)
    {
        return $this->Request($ico, 'premiumcz', $json);
    }

    //
    // Requests the detail for specified ico
    //
    // Returns: details or FALSE
    public function Request($ico, $type="detail", $json = false)
    {
        $detail = $this->DoRequest($type, array('ico' => $ico), $ico, $json);

        if(!$json) {
            switch($type) {
                case 'premiumcz':
                    $detail = $this->parsePremiumCZ($detail);
                    break;
                case 'detail':
                    $detail = $this->parseDetail($detail);
                    break;
                case 'basic':
                default:
                    $detail = $this->parseBasic($detail);
                    break;
            }
        }

        return $detail;
    }

    private function parseBasic($detail, $response = null)
    {
        if  ($detail === false) {
            return $detail;
        }

        $response = ($response == null) ? new BasicResult() : $response;
        $response->VatNumber            = (string)$detail->VatNumber;
        $response->TaxPayer             = (string)$detail->TaxPayer;

        return $this->parseAbstractResult($detail, $response);
    }

    private function parseDetail($detail, $response = null)
    {
        if  ($detail === false) {
            return $detail;
        }

        $response = ($response == null) ? new DetailResult() : $response;
        $response = $this->parseAbstractResult($detail, $response);
        $response->CzNaceCode           = (string)$detail->CzNaceCode;
        $response->CzNaceText           = (string)$detail->CzNaceText;
        $response->CzNaceDivision       = (string)$detail->CzNaceDivision;
        $response->CzNaceGroup          = (string)$detail->CzNaceGroup;
        $response->Created              = $this->parseDate($detail->Created);
        $response->Cancelled            = $this->parseDate($detail->Cancelled);
        $response->Activity             = (string)$detail->Activity;
        $response->Warning              = $this->parseBoolean($detail->Warning);
        $response->WarningUrl           = (string)$detail->WarningUrl;
        $response->LegalForm            = (string)$detail->LegalForm;
        $response->OwnershipType        = (string)$detail->OwnershipType;
        $response->EmployeeCount        = (string)$detail->EmployeeCount;

        return $response;
    }

    private function parsePremiumCZ($detail, $response = null)
    {
        if  ($detail === false) {
            return $detail;
        }
        
        $response = ($response == null) ? new PremiumCZResult() : $response;
        $response = $this->parseDetail($detail, $response);
        $response->VatNumber            = (string)$detail->VatNumber;
        $response->TaxPayer             = (string)$detail->TaxPayer;

        if (!empty($detail->BankAccounts)) {
            $response->BankAccounts = $this->parseObjectArray($detail->BankAccounts, 'BankAccount', function($c) {
                $o = new BankAccount();
                $o->AccountNumber = (string)$c->AccountNumber;
                $o->PublishedAt = $this->parseDate($c->PublishedAt);
                return $o;
            });
        }
        $response->LegalFormCode        = (string)$detail->LegalFormCode;
        $response->OwnershipCode        = (string)$detail->OwnershipCode;
        $response->UnReliability        = empty($detail->UnReliability) ? null : $this->parseBoolean($detail->UnReliability);
        $response->RegisterNumberText   = (string)$detail->RegisterNumberText;
        $response->TradeLicensingOffice = (string)$detail->TradeLicensingOffice;
        $response->ActualYear           = (int)"{$detail->ActualYear}";
        $response->SalesActual          = empty($detail->SalesActual) ? null : (float)"{$detail->SalesActual}";
        $response->ProfitActual         = empty($detail->ProfitActual) ? null : (float)"{$detail->ProfitActual}";
        $response->Sales                = (string)$detail->Sales;
        $response->Profit               = (string)$detail->Profit;
        return $response;
    }
}
