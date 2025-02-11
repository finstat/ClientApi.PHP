<?php
namespace FinstatApiCz;

require_once(__DIR__ . '/../FinStat.Client/Requests.php');
require_once(__DIR__ . '/../FinStat.Client/AbstractFinstatApi.php');
require_once(__DIR__ . '/../FinStat.Client/BaseFinstatApi.php');
require_once(__DIR__ . '/../FinStat.Client/ViewModel/AutoCompleteResult.php');
require_once(__DIR__ . '/../FinStat.Client/ViewModel/Detail/BankAccount.php');
require_once(__DIR__ . '/../FinStatCZ.ViewModel/Detail/BasicResult.php');
require_once(__DIR__ . '/../FinStatCZ.ViewModel/Detail/DetailResult.php');
require_once(__DIR__ . '/../FinStatCZ.ViewModel/Detail/PremiumCZResult.php');

class FinstatApi extends \BaseFinstatApi
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
        $response = $this->parseAbstractResult($detail, $response);

        return $response;
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
        $response->Warning              = "{$detail->Warning}"  == 'true' ;
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
            $response->BankAccounts = array();
            foreach ($detail->BankAccounts->BankAccount as $c) {
                $o = new \BankAccount();
                $o->AccountNumber = (string)$c->AccountNumber;
                $o->PublishedAt = $this->parseDate($c->PublishedAt);
                $response->BankAccounts[] = $o;
            }
        }
        $response->SuspendedAsPerson    = "{$detail->SuspendedAsPerson}" == 'true';
        $response->LegalFormCode        = (string)$detail->LegalFormCode;
        $response->OwnershipCode        = (string)$detail->OwnershipCode;
        $response->UnReliability        = empty($detail->UnReliability) ? null : "{$detail->UnReliability}" == 'true';
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
