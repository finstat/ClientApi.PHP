<?php
namespace FinStatCZ;

use FinStat\Client\BaseFinstatApi;
use FinStat\Client\ViewModel\Detail\BankAccount;
use FinStat\ViewModel\Detail\OfficeResult;
use FinStat\ViewModel\Detail\SubjectResult;
use FinStatCZ\ViewModel\Detail\BasicResult;
use FinStatCZ\ViewModel\Detail\DetailResult;
use FinStatCZ\ViewModel\Detail\PremiumCZResult;
use FinStatCZ\ViewModel\Detail\TurnoverCategoryResult;
use FinStatCZ\ViewModel\Detail\EliteCZResult;
use FinStatCZ\ViewModel\Detail\ContactResult;
use FinStatCZ\ViewModel\Detail\CzNaceResult;
use FinStatCZ\ViewModel\Detail\OfficesResult;
use FinStatCZ\ViewModel\Detail\VehicleFleetResult;
use FinStatCZ\ViewModel\Detail\VehicleFleetItemResult;
use FinStatCZ\ViewModel\Detail\VehicleFleetBrandResult;
use FinStatCZ\ViewModel\Detail\IsirProceedingResult;
use FinStatCZ\ViewModel\Detail\IsirPartyResult;
use FinStatCZ\ViewModel\Detail\ReceivableApplicationResult;
use FinStatCZ\ViewModel\Detail\IndicatorResult;
use FinStatCZ\ViewModel\Detail\IndicatorValueResult;
use FinStatCZ\ViewModel\Detail\UltimateCZResult;
use FinStatCZ\ViewModel\Detail\ORCRPersonResult;
use FinStatCZ\ViewModel\Detail\RZPPersonResult;
use FinStatCZ\ViewModel\Detail\CreditScoreResult;
use FinStatCZ\ViewModel\Detail\CreditScoreValueResult;
use FinStatCZ\ViewModel\Detail\FiledInsolvencyClaimsResult;
use FinStatCZ\ViewModel\Detail\FiledClaimApplicationResult;


class FinstatApi extends BaseFinstatApi
{
    public function RequestBasic($ico, $json = false, $lang = null)
    {
        return $this->Request($ico, 'basic', $json, $lang);
    }

    public function RequestDetail($ico, $json = false, $lang = null)
    {
        return $this->Request($ico, 'detail', $json, $lang);
    }

    public function RequestPremiumCZ($ico, $json = false, $lang = null)
    {
        return $this->Request($ico, 'premiumcz', $json, $lang);
    }

    public function RequestEliteCZ($ico, $json = false, $lang = null)
    {
        return $this->Request($ico, 'elitecz', $json, $lang);
    }

    public function RequestUltimateCZ($ico, $json = false, $lang = null)
    {
        return $this->Request($ico, 'ultimatecz', $json, $lang);
    }

    //
    // Requests the detail for specified ico
    //
    // $lang: language of response texts (sk|cs), null for the server default
    // Returns: details or FALSE
    public function Request($ico, $type="detail", $json = false, $lang = null)
    {
        $data = array('ico' => $ico);
        if (!empty($lang)) {
            $data['Lang'] = $lang;
        }
        $detail = $this->DoRequest($type, $data, $ico, $json);

        if(!$json) {
            switch($type) {
                case 'ultimatecz':
                    $detail = $this->parseUltimateCZ($detail);
                    break;
                case 'elitecz':
                    $detail = $this->parseEliteCZ($detail);
                    break;
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
        $response->SalesActual          = $this->parseNullableFloat($detail->SalesActual);
        $response->ProfitActual         = $this->parseNullableFloat($detail->ProfitActual);
        $response->Sales                = (string)$detail->Sales;
        $response->Profit               = (string)$detail->Profit;
        $response->SuspendedAsPersonUntil = $this->parseDate($detail->SuspendedAsPersonUntil);

        if (!empty($detail->TurnoverCategory)) {
            $o = new TurnoverCategoryResult();
            $o->Year = (int)"{$detail->TurnoverCategory->Year}";
            $o->Code = (string)$detail->TurnoverCategory->Code;
            $o->Text = (string)$detail->TurnoverCategory->Text;
            $response->TurnoverCategory = $o;
        }

        return $response;
    }

    private function parseEliteCZ($detail, $response = null)
    {
        if  ($detail === false) {
            return $detail;
        }

        $response = ($response == null) ? new EliteCZResult() : $response;
        $response = $this->parsePremiumCZ($detail, $response);

        $response->Contacts = $this->parseObjectArray($detail->Contacts, 'Contact', function($c) {
            $o = new ContactResult();
            $o->Contact = (string)$c->Contact;
            $o->Type    = (string)$c->Type;
            $o->Sources = $this->parseStringArray($c->Sources);
            return $o;
        });

        $response->OtherCzNaces = $this->parseObjectArray($detail->OtherCzNaces, 'CzNace', function($c) {
            $o = new CzNaceResult();
            $o->Code = (string)$c->Code;
            $o->Text = (string)$c->Text;
            return $o;
        });

        if (!empty($detail->Offices)) {
            $o = new OfficesResult();
            $o->Count = (int)"{$detail->Offices->Count}";
            $o->Items = $this->parseObjectArray($detail->Offices->Items, 'Office', function($office) {
                $oo = new OfficeResult();
                $oo = $this->parseAddress($office, $oo);
                $oo->Name     = $this->parseNullableString($office->Name);
                $oo->Type     = (string)$office->Type;
                $oo->Subjects = $this->parseStringArray($office->Subjects);
                return $oo;
            });
            $response->Offices = $o;
        }

        if (!empty($detail->VehicleFleet)) {
            $response->VehicleFleet = $this->parseVehicleFleet($detail->VehicleFleet);
        }

        $response->IsirProceedings = $this->parseObjectArray($detail->IsirProceedings, 'IsirProceeding', function($p) {
            return $this->parseIsirProceeding($p);
        });

        $response->FinancialIndicators = $this->parseObjectArray($detail->FinancialIndicators, 'Indicator', function($i) {
            $o = new IndicatorResult();
            $o->Name   = (string)$i->Name;
            $o->Values = $this->parseObjectArray($i->Values, 'IndicatorValue', function($v) {
                $ov = new IndicatorValueResult();
                $ov->Year  = (int)"{$v->Year}";
                $ov->Value = $this->parseNullableFloat($v->Value);
                return $ov;
            });
            return $o;
        });

        return $response;
    }

    private function parseUltimateCZ($detail, $response = null)
    {
        if  ($detail === false) {
            return $detail;
        }

        $response = ($response == null) ? new UltimateCZResult() : $response;
        $response = $this->parseEliteCZ($detail, $response);

        $response->ORCRPersons = $this->parseObjectArray($detail->ORCRPersons, 'ORCRPerson', function($person) {
            $o = $this->parsePerson($person, new ORCRPersonResult());
            $o->DepositAmount           = $this->parseNullableFloat($person->DepositAmount);
            $o->PartnersSharePercentage = $this->parseNullableFloat($person->PartnersSharePercentage);
            $o->PaybackRange            = $this->parseNullableFloat($person->PaybackRange);
            $o->Ico                     = $this->parseNullableString($person->Ico);
            return $o;
        });

        $response->RZPPersons = $this->parseObjectArray($detail->RZPPersons, 'RZPPerson', function($person) {
            $o = new RZPPersonResult();
            $o->FullName     = (string)$person->FullName;
            $o->BirthDate    = $this->parseDate($person->BirthDate);
            $o->Function     = (string)$person->Function;
            $o->FunctionFrom = $this->parseDate($person->FunctionFrom);
            $o->Subjects     = $this->parseStringArray($person->Subjects);
            return $o;
        });

        $response->StatutoryAction   = (string)$detail->StatutoryAction;
        $response->ProcurationAction = (string)$detail->ProcurationAction;

        $response->Subjects = $this->parseObjectArray($detail->Subjects, 'Subject', function($subject) {
            $o = new SubjectResult();
            $o->Title         = (string)$subject->Title;
            $o->ValidFrom     = $this->parseDate($subject->ValidFrom);
            $o->SuspendedFrom = $this->parseDate($subject->SuspendedFrom);
            $o->SuspendedTo   = $this->parseDate($subject->SuspendedTo);
            return $o;
        });

        $response->CreditScores = $this->parseObjectArray($detail->CreditScores, 'CreditScore', function($c) {
            $o = new CreditScoreResult();
            $o->Name   = (string)$c->Name;
            $o->Values = $this->parseObjectArray($c->Values, 'CreditScoreValue', function($v) {
                $ov = new CreditScoreValueResult();
                $ov->Year  = (int)"{$v->Year}";
                $ov->Value = $this->parseNullableFloat($v->Value);
                $ov->State = $this->parseNullableString($v->State);
                return $ov;
            });
            return $o;
        });

        if (!empty($detail->FiledInsolvencyClaims)) {
            $claims = $detail->FiledInsolvencyClaims;
            $o = new FiledInsolvencyClaimsResult();
            $o->ProceedingsCount = (int)"{$claims->ProceedingsCount}";
            $o->AmountOfClaims   = $this->parseNullableFloat($claims->AmountOfClaims);
            $o->Applications     = $this->parseObjectArray($claims->Applications, 'FiledClaimApplication', function($a) {
                $oa = new FiledClaimApplicationResult();
                $oa->FileReference  = (string)$a->FileReference;
                $oa->Url            = (string)$a->Url;
                $oa->Debtors        = $this->parseIsirParties($a->Debtors);
                $oa->Representative = $this->parseIsirParty($a->Representative);
                $oa->PublishDate    = $this->parseDate($a->PublishDate);
                $oa->ClaimsCount    = $this->parseNullableInt($a->ClaimsCount);
                $oa->TotalAmount    = $this->parseNullableFloat($a->TotalAmount);
                return $oa;
            });
            $response->FiledInsolvencyClaims = $o;
        }

        return $response;
    }

    private function parseVehicleFleet($fleet)
    {
        $o = new VehicleFleetResult();
        $o->Year         = (int)"{$fleet->Year}";
        $o->ActiveCount  = (int)"{$fleet->ActiveCount}";
        $o->Url          = (string)$fleet->Url;
        $o->VehicleCount = $this->parseNullableInt($fleet->VehicleCount);

        $parseItem = function($i) {
            $oi = new VehicleFleetItemResult();
            $oi->Type  = (string)$i->Type;
            $oi->Count = (int)"{$i->Count}";
            return $oi;
        };
        $o->OwnershipTypes  = $this->parseObjectArray($fleet->OwnershipTypes, 'VehicleFleetItem', $parseItem);
        $o->VehicleTypes    = $this->parseObjectArray($fleet->VehicleTypes, 'VehicleFleetItem', $parseItem);
        $o->ProductionYears = $this->parseObjectArray($fleet->ProductionYears, 'VehicleFleetItem', $parseItem);
        $o->TopBrands       = $this->parseObjectArray($fleet->TopBrands, 'VehicleFleetBrand', function($b) {
            $ob = new VehicleFleetBrandResult();
            $ob->Name  = (string)$b->Name;
            $ob->Count = (int)"{$b->Count}";
            return $ob;
        });

        return $o;
    }

    private function parseIsirProceeding($p)
    {
        $o = new IsirProceedingResult();
        $o->FileReference  = (string)$p->FileReference;
        $o->Url            = (string)$p->Url;
        $o->Court          = $this->parseNullableString($p->Court);
        $o->FirstEvent     = $this->parseDate($p->FirstEvent);
        $o->LastEvent      = $this->parseDate($p->LastEvent);
        $o->Status         = (string)$p->Status;
        $o->Debtors        = $this->parseIsirParties($p->Debtors);

        // UltimateCZ only, the elements are missing in EliteCZ
        $o->Type           = $this->parseNullableString($p->Type);
        $o->StartDate      = $this->parseDate($p->StartDate);
        $o->Officers       = $this->parseIsirParties($p->Officers);
        $o->CreditorsCount = $this->parseNullableInt($p->CreditorsCount);
        $o->Creditors      = $this->parseIsirParties($p->Creditors);
        $o->AmountOfClaims = $this->parseNullableFloat($p->AmountOfClaims);
        $o->ReceivableApplications = $this->parseObjectArray($p->ReceivableApplications, 'ReceivableApplication', function($a) {
            $oa = new ReceivableApplicationResult();
            $oa->Creditor       = $this->parseIsirParty($a->Creditor);
            $oa->Representative = $this->parseIsirParty($a->Representative);
            $oa->PublishDate    = $this->parseDate($a->PublishDate);
            $oa->ClaimsCount    = $this->parseNullableInt($a->ClaimsCount);
            $oa->TotalAmount    = $this->parseNullableFloat($a->TotalAmount);
            return $oa;
        });

        return $o;
    }

    private function parseIsirParties($element)
    {
        return $this->parseObjectArray($element, 'IsirParty', function($p) {
            return $this->parseIsirParty($p);
        });
    }

    private function parseIsirParty($element)
    {
        if (empty($element)) {
            return null;
        }

        $o = new IsirPartyResult();
        $o->Name      = (string)$element->Name;
        $o->Ico       = $this->parseNullableString($element->Ico);
        $o->BirthDate = $this->parseDate($element->BirthDate);
        return $o;
    }

    private function parseNullableFloat($element)
    {
        return empty($element) ? null : (float)"{$element}";
    }

    private function parseNullableInt($element)
    {
        return empty($element) ? null : (int)"{$element}";
    }
}
