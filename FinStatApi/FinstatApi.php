<?php

require_once(__DIR__ . '/../FinStat.Client/Requests.php');
require_once(__DIR__ . '/../FinStat.Client/AbstractFinstatApi.php');
require_once(__DIR__ . '/../FinStat.Client/BaseFinstatApi.php');
require_once(__DIR__ . '/../FinStat.Client/ViewModel/AutoCompleteResult.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Detail/BaseResult.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Detail/DetailResult.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Detail/ExtendedResult.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Detail/UltimateResult.php');

class FinstatApi extends BaseFinstatApi
{
    public function RequestDetail($ico, $json = false)
    {
        return $this->Request($ico, 'detail', $json);
    }

    public function RequestExtended($ico, $json = false)
    {
        return $this->Request($ico, 'extended', $json);
    }

    public function RequestUltimate($ico, $json = false)
    {
        return $this->Request($ico, 'ultimate', $json);
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
        }

        return $detail;
    }

    protected function parseBase($detail , $response = null)
    {
        $response = ($response == null)? new BaseResult() : $response;
        $response = $this->parseAbstractBase($detail, $response);
        $response->RegisterNumberText   = (string)$detail->RegisterNumberText;
        $response->Dic                  = (string)$detail->Dic;
        $response->SuspendedAsPerson    = "{$detail->SuspendedAsPerson}"  == 'true' ;
        $response->PaymentOrderWarning  = "{$detail->PaymentOrderWarning}"  == 'true';
        $response->PaymentOrderUrl      = (string)$detail->PaymentOrderUrl;
        $response->OrChange             = "{$detail->OrChange}"  == 'true';
        $response->OrChangeUrl          = (string)$detail->OrChangeURL;
        $response->SkNaceCode           = (string)$detail->SkNaceCode;
        $response->SkNaceText           = (string)$detail->SkNaceText;
        $response->SkNaceDivision       = (string)$detail->SkNaceDivision;
        $response->SkNaceGroup          = (string)$detail->SkNaceGroup;
        $response->LegalFormCode        = (string)$detail->LegalFormCode;
        $response->LegalFormText        = (string)$detail->LegalFormText;
        $response->RpvsInsert           = (string)$detail->RpvsInsert;
        $response->RpvsUrl              = (string)$detail->RpvsUrl;
        $response->SalesCategory        = (string)$detail->SalesCategory;
        $response->RevenueActual        = empty($detail->RevenueActual) ? null :(double)"{$detail->RevenueActual}";
        $response->ProfitActual         = empty($detail->ProfitActual) ? null :(double)"{$detail->ProfitActual}";

        if (!empty($detail->IcDphAdditional)) {
            $response->IcDphAdditional = $this->parseIcDphAdditional($detail->IcDphAdditional);
        }

        if (!empty($detail->JudgementIndicators)) {
            $response->JudgementIndicators = array();
            foreach ($detail->JudgementIndicators->JudgementIndicator as $c) {
                $o = new JudgementIndicatorResult();
                $o->Name = (string) $c->Name;
                $o->Value = "{$c->Value}"  == 'true';
                $response->JudgementIndicators[] = $o;
            }
        }
        $response->JudgementFinstatLink =  (string)$detail->JudgementFinstatLink;

        return $response;
    }

    private function parseDetail($detail , $response = null)
    {
        $response = ($response == null)? new DetailResult() : $response;
        $this->parseBase($detail, $response);

        $response->Revenue              = (string)$detail->Revenue;
        $response->Profit               = (string)$detail->Profit;

        return $response;
    }

    private function parseExtended($detail, $response = null)
    {
        $response = ($response == null)? new ExtendedResult() : $response;
        $response = $this->parseBase($detail, $response);

        $response->EmployeeCode         = (string)$detail->EmployeeCode;
        $response->EmployeeText         = (string)$detail->EmployeeText;
        $response->OwnershipTypeCode    = (string)$detail->OwnershipTypeCode;
        $response->OwnershipTypeText    = (string)$detail->OwnershipTypeText;
        $response->ActualYear           = (int)"{$detail->ActualYear}";
        $response->CreditScoreValue     = (float)$detail->CreditScoreValue;
        $response->CreditScoreState     = (string)$detail->CreditScoreState;
        $response->BasicCapital         = (!empty($detail->BasicCapital)) ? (float)$detail->BasicCapital : null;
        $response->RevenuePrev          = empty($detail->RevenuePrev) ? null :(double)"{$detail->RevenuePrev}";
        $response->ProfitPrev           = empty($detail->ProfitPrev) ? null :(double)"{$detail->ProfitPrev}";
        $response->ForeignResources     = empty($detail->ForeignResources) ? null :(double)"{$detail->ForeignResources}";
        $response->GrossMargin          = empty($detail->GrossMargin) ? null :(double)"{$detail->GrossMargin}";
        $response->ROA                  = empty($detail->ROA) ? null : (double)"{$detail->ROA}";
        $response->WarningKaR           = $this->parseDate($detail->WarningKaR);
        $response->WarningLiquidation   = $this->parseDate($detail->WarningLiquidation);
        $response->KaRUrl               = (string)$detail->KaRUrl;
        $response->DebtUrl              = (string)$detail->DebtUrl;
        $response->DisposalUrl          = (string)$detail->DisposalUrl;
        $response->HasKaR               = "{$detail->HasKaR}"  == 'true';
        $response->HasDebt              = "{$detail->HasDebt}"  == 'true';
        $response->HasDisposal          = "{$detail->HasDisposal}"  == 'true';
        $response->SelfEmployed         = "{$detail->SelfEmployed}"  == 'true';

        $response->Phones = array();
        if (!empty($detail->Phones)) {
            foreach ($detail->Phones->string as $s) {
                $response->Phones[] = (string)$s;
            }
        }

        $response->Emails = array();
        if (!empty($detail->Emails)) {
            foreach ($detail->Emails->string as $s) {
                $response->Emails[] = (string)$s;
            }
        }

        $response->Debts = array();
        if (!empty($detail->Debts)) {
            foreach ($detail->Debts->Debt as $debt) {
                $response->Debts[] = $this->parseDebtResult($debt);
            }
        }

        $response->StateReceivables = array();
        if (!empty($detail->StateReceivables)) {
            foreach ($detail->StateReceivables->ReceivableDebt as $debt) {
                $response->StateReceivables[] = $this->ParseReceivableDebtResult($debt);
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

        if (!empty($detail->Offices)) {
            $response->Offices = array();
            foreach ($detail->Offices->Office as $office) {
                $o = new OfficeResult();
                $o = $this->parseAddress($office, $o);
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

        if (!empty($detail->Subjects)) {
            $response->Subjects = array();
            foreach ($detail->Subjects->Subject as $subject) {
                $o = new SubjectResult();
                $o->Title = (string)$subject->Title;
                $o->ValidFrom = $this->parseDate($subject->ValidFrom);
                $o->SuspendedFrom = $this->parseDate($subject->SuspendedFrom);
                $response->Subjects[] = $o;
           }
        }


        if($response->SelfEmployed && !empty($detail->StructuredName)) {
            $response->StructuredName = $this->parseStructuredName($detail->StructuredName);
        }

        if (!empty($detail->ContactSources)) {
            $response->ContactSources = array();
            foreach ($detail->ContactSources->ContactSource as $c) {
               $o = new ContactSourceResult();
               $o->Contact = (string) $c->Contact;
               if(!empty($c->Sources)) {
                   $o->Sources = array();
                   foreach ($c->Sources->string as $s) {
                      $o->Sources[] = (string)$s;
                   }
               }
               $response->ContactSources[] = $o;
            }
        }

        if (!empty($detail->Ratios)) {
            $response->Ratios = array();
            foreach ($detail->Ratios->Ratio as $c) {
                $o = new RatioResult();
                $o->Name = (string) $c->Name;
                if (!empty($c->Values)) {
                    foreach ($c->Values->Item as $v) {
                        $ov = new  RatioItemResult();
                        $ov->Year = (int)$v->Year;
                        $ov->Value = (float)$v->Value;
                        $o->Values[] = $ov;
                    }
                }
                $response->Ratios[] = $o;
            }
        }

        if (!empty($detail->JudgementCounts)) {
            $response->JudgementCounts = array();
            foreach ($detail->JudgementCounts->JudgementCount as $c) {
                $o = new JudgementCountResult();
                $o->Name = (string) $c->Name;
                $o->Value = (int) $c->Value;
                $response->JudgementCounts[] = $o;
            }
        }

        $response->JudgementLastPublishedDate = $this->parseDate($detail->JudgementLastPublishedDate);

        return $response;
    }

    private function parseUltimate($detail)
    {
        if ($detail === FALSE) {
            return $detail;
        }

        $response = $this->parseExtended($detail, new UltimateResult());
        if ($response !== FALSE) {
            $response->EmployeesNumber = (!empty($detail->EmployeesNumber)) ? (int)$detail->EmployeesNumber : null;
            $response->ORSection = (string)$detail->ORSection;
            $response->ORInsertNo = (string)$detail->ORInsertNo;
            $response->PaybackRange  = (!empty($detail->PaybackRange)) ? (float)$detail->PaybackRange : null;
            $response->Persons = array();
            if (!empty($detail->Persons)) {
                foreach ($detail->Persons->Person as $person) {
                    $o = $this->parsePerson($person);
                    $o->DepositAmount  = (!empty($person->DepositAmount)) ? (float)$person->DepositAmount : null;
                    $o->PaybackRange  = (!empty($person->PaybackRange)) ? (float)$person->PaybackRange : null;
                    $response->Persons[] = $o;
                }
            }

            $response->RpvsPersons = array();
            if (!empty($detail->RpvsPersons)) {
                foreach ($detail->RpvsPersons->RpvsPerson as $rpvsPerson) {
                    $o = $this->parsePerson($rpvsPerson, new RpvsPersonResult());
                    $o->BirthDate = (!empty($rpvsPerson->BirthDate)) ? $this->parseDate($rpvsPerson->BirthDate) : null;
                    $o->Ico = (!empty($rpvsPerson->Ico)) ? (string)$rpvsPerson->Ico : null;
                    $response->RpvsPersons[] = $o;
                }
            }

            if (!empty($detail->RegistrationCourt)) {
                $o = new PersonResult();
                $o = $this->parseAddress($detail->RegistrationCourt, $o);
                $o->Name = (string)$detail->RegistrationCourt->Name;
                $response->RegistrationCourt = $o;
            }

            if (!empty($detail->WebPages)) {
                $response->WebPages = array();
                foreach ($detail->WebPages->string as $s) {
                   $response->WebPages[] = (string)$s;
                }
            }

            if (!empty($detail->StatutoryAction)) {
                $response->StatutoryAction = (string)$detail->StatutoryAction;
            }

            if (!empty($detail->ProcurationAction)) {
                $response->ProcurationAction = (string)$detail->ProcurationAction;
            }

            if (!empty($detail->AddressHistory)) {
                $response->AddressHistory = array();
                foreach ($detail->AddressHistory->HistoryAddress as $address) {
                    $o = new AddressResult();
                    $o = $this->parseAddress($address, $o);
                    $o->ValidFrom = $this->parseDate($address->ValidFrom);
                    $o->ValidTo = $this->parseDate($address->ValidTo);
                    $response->AddressHistory[] = $o;
                }
            }

            if (!empty($detail->ORCancelled)) {
                $response->ORCancelled = $this->parseDate($detail->ORCancelled);
            }

            if (!empty($detail->Bankrupt)) {
                $response->Bankrupt = $this->ParseProceeding($detail->Bankrupt, new BankruptResult());
            }

            if (!empty($detail->Restructuring)) {
                $response->Restructuring = $this->ParseProceeding($detail->Restructuring, new RestructuringResult());
            }

            if (!empty($detail->Liquidation)) {
                $response->Liquidation = $this->arseLiquidationResult($detail->Liquidation);
            }

			if (!empty($detail->OtherProceeding)) {
                $response->OtherProceeding = $this->ParseProceeding($detail->OtherProceeding, new ProceedingResult());
            }
        }

        return $response;
    }

    protected function ParseDebtResult($detail, $response = null) {
        if(!empty($detail)) {
            $o = (!empty($response)) ? $response : new DebtResult();
            $o->Source  = $detail->Source;
            $o->Value   = (double)$detail->Value;
            $o->ValidFrom  = $this->parseDate($detail->ValidFrom);
            return $o;
        }
        return null;
    }

    protected function ParseReceivableDebtResult($detail, $response = null) {
        if(!empty($detail)) {
            $o = (!empty($response)) ? $response : new ReceivableDebtResult();
            return $this->ParseDebtResult($detail, $o);
        }
        return null;
    }

    protected function ParseLiquidationResult($detail, $response = null) {
        if(!empty($detail)) {
            $o = (!empty($response)) ? $response : new LiquidationResult();
            $o->Source = (string) $detail->Source;
            $o->EnterDate = $this->parseDate($detail->EnterDate);
            $o->EnterReason = (string) $detail->EnterReason;
            $o->ExitDate = $this->parseDate($detail->ExitDate);
            $o->Officer = $this->parsePerson($detail->Officer);
            if (!empty($detail->Deadlines)) {
                foreach ($detail->Deadlines->Deadline as $deadline) {
                    $od = new DeadlineResult();
                    $od->Date  = (!empty($deadline->Date)) ? $this->parseDate($deadline->Date) : null;
                    $od->Type  = (!empty($deadline->Type)) ? (string)$deadline->Type : null;
                    $o->Deadlines[] = $od;
                }
            }
            return $o;
        }
        return null;
    }

    protected function ParseProceeding($detail, $response = null) {
        if(!empty($detail)) {
            $o = (!empty($response)) ? $response : new ProceedingResult();
            $o = $this->ParseLiquidationResult($detail, $response);
            if(!empty($o)) {
                $o->FileReference = (string) $detail->FileReference;
                $o->CourtCode = (string) $detail->CourtCode;
                $o->StartDate = $this->parseDate($detail->OtherProceeding->StartDate);
                $o->ExitReason = (string) $detail->OtherProceeding->ExitReason;
                $o->Status = (string) $detail->OtherProceeding->Status;
            }
            return $o;
        }
        return null;
    }
}