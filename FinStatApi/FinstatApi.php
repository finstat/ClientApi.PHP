<?php

namespace FinStat\Api;

use FinStat\Client\BaseFinstatApi;
use FinStat\ViewModel\Detail\BasicResult;
use FinStat\ViewModel\Detail\DetailResult;
use FinStat\ViewModel\Detail\ExtendedResult;
use FinStat\ViewModel\Detail\UltimateResult;

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
        if (!$json) {
            switch ($type) {
                case 'ultimate':
                    $detail = $this->parseUltimate($detail);
                    break;
                case 'extended':
                    $detail = $this->parseExtended($detail);
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

    protected function parseBase($detail, $response = null)
    {
        $response = ($response == null) ? new BaseResult() : $response;
        $response = $this->parseCommonResult($detail, $response);
        $response->RegisterNumberText       = (string)$detail->RegisterNumberText;
        $response->Dic                  = (string)$detail->Dic;
        $response->IcDPH                = (string)$detail->IcDPH;

        $response->SuspendedAsPersonUntil   = $this->parseDate($detail->SuspendedAsPersonUntil);
        $response->PaymentOrderWarning      = $this->parseBoolean($detail->PaymentOrderWarning);
        $response->PaymentOrderUrl          = (string)$detail->PaymentOrderUrl;
        $response->OrChange                 = $this->parseBoolean($detail->OrChange);
        $response->OrChangeUrl              = (string)$detail->OrChangeURL;
        $response->SkNaceCode               = (string)$detail->SkNaceCode;
        $response->SkNaceText               = (string)$detail->SkNaceText;
        $response->SkNaceDivision           = (string)$detail->SkNaceDivision;
        $response->SkNaceGroup              = (string)$detail->SkNaceGroup;
        $response->LegalFormCode            = (string)$detail->LegalFormCode;
        $response->LegalFormText            = (string)$detail->LegalFormText;
        $response->RpvsInsert               = (string)$detail->RpvsInsert;
        $response->RpvsUrl                  = (string)$detail->RpvsUrl;
        $response->SalesCategory            = (string)$detail->SalesCategory;
        $response->RevenueActual            = empty($detail->RevenueActual) ? null : (float)"{$detail->RevenueActual}";
        $response->ProfitActual             = empty($detail->ProfitActual) ? null : (float)"{$detail->ProfitActual}";

        if (!empty($detail->IcDphAdditional)) {
            $response->IcDphAdditional = $this->parseIcDphAdditional($detail->IcDphAdditional);
        }

        if (!empty($detail->JudgementIndicators)) {
            $response->JudgementIndicators = $this->parseObjectArray($detail->JudgementIndicators, 'JudgementIndicator', function($c) {
                $o = new JudgementIndicatorResult();
                $o->Name = (string) $c->Name;
                $o->Value = $this->parseBoolean($c->Value);
                return $o;
            });
        }
        $response->JudgementFinstatLink =  (string)$detail->JudgementFinstatLink;

        $response->KaRUrl               = (string)$detail->KaRUrl;
        $response->DebtUrl              = (string)$detail->DebtUrl;
        $response->HasKaR               = $this->parseBoolean($detail->HasKaR);
        $response->HasDebt              = $this->parseBoolean($detail->HasDebt);
        $response->Anonymized           = $this->parseBoolean($detail->Anonymized);
        if (!empty($detail->BankAccounts)) {
            $response->BankAccounts = array();
            foreach ($detail->BankAccounts->BankAccount as $c) {
                $o = new BankAccount();
                $o->AccountNumber = (string)$c->AccountNumber;
                $o->PublishedAt = $this->parseDate($c->PublishedAt);
                $response->BankAccounts[] = $o;
            }
        }
        $response->TaxReliabilityIndex  = "{$detail->TaxReliabilityIndex}";

        return $response;
    }

    private function parseBasic($detail)
    {
        if ($detail === false) {
            return $detail;
        }

        $response = new BasicResult();
        $response = $this->parseAbstractResult($detail, $response);

        $response->Anonymized           = $this->parseBoolean($detail->Anonymized);
        $response->Dic                  = (string)$detail->Dic;
        $response->IcDPH                = (string)$detail->IcDPH;
        $response->Paragraph            = (string)$detail->Paragraph;         
        return $response;
    }

    private function parseDetail($detail, $response = null)
    {
        $response = ($response == null) ? new DetailResult() : $response;
        $this->parseBase($detail, $response);

        $response->Revenue              = (string)$detail->Revenue;
        $response->Profit               = (string)$detail->Profit;

        return $response;
    }

    private function parseExtended($detail, $response = null)
    {
        $response = ($response == null) ? new ExtendedResult() : $response;
        $response = $this->parseBase($detail, $response);

        $response->EmployeeCode                 = (string)$detail->EmployeeCode;
        $response->EmployeeText                 = (string)$detail->EmployeeText;
        $response->OwnershipTypeCode            = (string)$detail->OwnershipTypeCode;
        $response->OwnershipTypeText            = (string)$detail->OwnershipTypeText;
        $response->ActualYear                   = (int)"{$detail->ActualYear}";
        $response->CreditScoreValue             = (float)$detail->CreditScoreValue;
        $response->CreditScoreState             = (string)$detail->CreditScoreState;
        $response->BasicCapital                 = (!empty($detail->BasicCapital)) ? (float)$detail->BasicCapital : null;
        $response->RevenuePrev                  = empty($detail->RevenuePrev) ? null : (float)"{$detail->RevenuePrev}";
        $response->ProfitPrev                   = empty($detail->ProfitPrev) ? null : (float)"{$detail->ProfitPrev}";
        $response->ForeignResources             = empty($detail->ForeignResources) ? null : (float)"{$detail->ForeignResources}";
        $response->GrossMargin                  = empty($detail->GrossMargin) ? null : (float)"{$detail->GrossMargin}";
        $response->ROA                          = empty($detail->ROA) ? null : (float)"{$detail->ROA}";
        $response->WarningKaR                   = $this->parseDate($detail->WarningKaR);
        $response->WarningLiquidation           = $this->parseDate($detail->WarningLiquidation);
        $response->DisposalUrl                  = (string)$detail->DisposalUrl;
        $response->HasDisposal                  = $this->parseBoolean($detail->HasDisposal);
        $response->SelfEmployed                 = $this->parseBoolean($detail->SelfEmployed);
        $response->CreditScoreValueIndex05      = (float)$detail->CreditScoreValueIndex05;
        $response->CreditScoreStateIndex05      = (string)$detail->CreditScoreStateIndex05;
        $response->CreditScoreValueFinStatScore = (float)$detail->CreditScoreValueFinStatScore;
        $response->CreditScoreStateFinStatScore = (string)$detail->CreditScoreStateFinStatScore;

        $response->Phones = $this->parseStringArray($detail->Phones);

        $response->Emails = $this->parseStringArray($detail->Emails);

        $response->Debts = $this->parseObjectArray($detail->Debts, 'Debt', function($debt) {
            return $this->parseDebtResult($debt);
        });

        $response->StateReceivables = $this->parseObjectArray($detail->StateReceivables, 'ReceivableDebt', function($debt) {
            return $this->ParseReceivableDebtResult($debt);
        });

        $response->CommercialReceivables = $this->parseObjectArray($detail->CommercialReceivables, 'ReceivableDebt', function($debt) {
            return $this->ParseReceivableDebtResult($debt);
        });

        $response->PaymentOrders = $this->parseObjectArray($detail->PaymentOrders, 'PaymentOrder', function($paymentOrder) {
            $o = new PaymentOrderResult();
            $o->PublishDate  = $this->parseDate($paymentOrder->PublishDate);
            $o->Value   = (float)$paymentOrder->Value;
            return $o;
        });

        if (!empty($detail->Offices)) {
            $response->Offices = $this->parseObjectArray($detail->Offices, 'Office', function($office) {
                $o = new OfficeResult();
                $o = $this->parseAddress($office, $o);
                $o->Type = (string)$office->Type;
                $o->Subjects = $this->parseStringArray($office->Subjects);
                return $o;
            });
        }

        if (!empty($detail->Subjects)) {
            $response->Subjects = $this->parseObjectArray($detail->Subjects, 'Subject', function($subject) {
                $o = new SubjectResult();
                $o->Title = (string)$subject->Title;
                $o->ValidFrom = $this->parseDate($subject->ValidFrom);
                $o->SuspendedFrom = $this->parseDate($subject->SuspendedFrom);
                $o->SuspendedTo = $this->parseDate($subject->SuspendedTo);
                return $o;
            });
        }


        if ($response->SelfEmployed && !empty($detail->StructuredName)) {
            $response->StructuredName = $this->parseStructuredName($detail->StructuredName);
        }

        if (!empty($detail->ContactSources)) {
            $response->ContactSources = $this->parseObjectArray($detail->ContactSources, 'ContactSource', function($c) {
                $o = new ContactSourceResult();
                $o->Contact = (string) $c->Contact;
                $o->Sources = $this->parseStringArray($c->Sources);
                return $o;
            });
        }

        if (!empty($detail->Ratios)) {
            $response->Ratios = $this->parseObjectArray($detail->Ratios, 'Ratio', function($c) {
                $o = new RatioResult();
                $o->Name = (string) $c->Name;
                $o->Values = $this->parseObjectArray($c->Values, 'Item', function($v) {
                    $ov = new  RatioItemResult();
                    $ov->Year = (int)$v->Year;
                    $ov->Value = (float)$v->Value;
                    return $ov;
                });
                return $o;
            });
        }

        if (!empty($detail->JudgementCounts)) {
            $response->JudgementCounts = $this->parseObjectArray($detail->JudgementCounts, 'JudgementCount', function($c) {
                $o = new JudgementCountResult();
                $o->Name = (string) $c->Name;
                $o->Value = (int) $c->Value;
                return $o;
            });
        }

        $response->JudgementLastPublishedDate = $this->parseDate($detail->JudgementLastPublishedDate);

        if (!empty($detail->DistraintsAuthorization)) {
            $response->DistraintsAuthorization = new DistraintsAuthorizationInfoResult();
            $response->DistraintsAuthorization->LastPublishDate = $this->parseDate($detail->DistraintsAuthorization->LastPublishDate);
            $response->DistraintsAuthorization->Count = (int)$detail->DistraintsAuthorization->Count;
        }

        return $response;
    }

    private function parseUltimate($detail)
    {
        if ($detail === false) {
            return $detail;
        }


        $response = $this->parseExtended($detail, new UltimateResult());
        if ($response !== false) {
            $response->EmployeesNumber = (!empty($detail->EmployeesNumber)) ? (int)$detail->EmployeesNumber : null;
            $response->ORSection = (string)$detail->ORSection;
            $response->ORInsertNo = (string)$detail->ORInsertNo;
            $response->PaybackRange  = (!empty($detail->PaybackRange)) ? (float)$detail->PaybackRange : null;
            $response->Persons = $this->parseObjectArray($detail->Persons, 'Person', function($person) {
                $o = $this->parsePerson($person);
                $o->DepositAmount  = (!empty($person->DepositAmount)) ? (float)$person->DepositAmount : null;
                $o->PartnersSharePercentage  = (!empty($person->PartnersSharePercentage)) ? (float)$person->PartnersSharePercentage : null;
                $o->PaybackRange  = (!empty($person->PaybackRange)) ? (float)$person->PaybackRange : null;
                return $o;
            });

            $response->RpvsPersons = $this->parseObjectArray($detail->RpvsPersons, 'RpvsPerson', function($rpvsPerson) {
                $o = $this->parsePerson($rpvsPerson, new RpvsPersonResult());
                $o->Ico = (!empty($rpvsPerson->Ico)) ? (string)$rpvsPerson->Ico : null;
                return $o;
            });

            $response->RPOPersons = $this->parseObjectArray($detail->RPOPersons, 'RPOPerson', function($rpoPerson) {
                $o = new RPOPersonResult();
                $o->BirthDate = (!empty($rpoPerson->BirthDate)) ? $this->parseDate($rpoPerson->BirthDate) : null;
                $o->Citizenship = (!empty($rpoPerson->Citizenship)) ? (string)$rpoPerson->Citizenship : null;
                $o->FullName = (string)$rpoPerson->FullName;
                $o->Country = (string)$rpoPerson->Country;
                $o->DetectedFrom = $this->parseDate($rpoPerson->DetectedFrom);
                $o->DetectedTo  = $this->parseDate($rpoPerson->DetectedTo);
                $o->Functions = $this->parseObjectArray($rpoPerson->Functions, 'FunctionAssigment', function($function) {
                    $of = new FunctionResult();
                    $of->Type = (string)$function->Type;
                    $of->Description = (string)$function->Description;
                    $of->From = $this->parseDate($function->From);
                    return $of;
                });
                if (!empty($rpoPerson->StructuredName)) {
                    $o->StructuredName = $this->parseStructuredName($rpoPerson->StructuredName);
                }

                return $o;
            });

            if (!empty($detail->RegistrationCourt)) {
                $o = new PersonResult();
                $o = $this->parseAddress($detail->RegistrationCourt, $o);
                $o->Name = (string)$detail->RegistrationCourt->Name;
                $response->RegistrationCourt = $o;
            }

            if (!empty($detail->WebPages)) {
                $response->WebPages = $this->parseStringArray($detail->WebPages);
            }

            if (!empty($detail->StatutoryAction)) {
                $response->StatutoryAction = (string)$detail->StatutoryAction;
            }

            if (!empty($detail->ProcurationAction)) {
                $response->ProcurationAction = (string)$detail->ProcurationAction;
            }

            if (!empty($detail->AddressHistory)) {
                $response->AddressHistory = $this->parseObjectArray($detail->AddressHistory, 'HistoryAddress', function($address) {
                    $o = new HistoryAddressResult();
                    $o = $this->parseAddress($address, $o);
                    $o->ValidFrom = $this->parseDate($address->ValidFrom);
                    $o->ValidTo = $this->parseDate($address->ValidTo);
                    return $o;
                });
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
            
            if (!empty($detail->PreventiveRestructuring)) {
                $response->PreventiveRestructuring = new PreventiveRestructuringResult();
                $response->PreventiveRestructuring->Source = (string) $detail->PreventiveRestructuring->Source;
                $response->PreventiveRestructuring->FileReference = (string) $detail->PreventiveRestructuring->FileReference;
                $response->PreventiveRestructuring->CourtCode = (string) $detail->PreventiveRestructuring->CourtCode;
                $response->PreventiveRestructuring->FirstDate = $this->parseDate($detail->PreventiveRestructuring->FirstDate);
                $response->PreventiveRestructuring->LastDate = $this->parseDate($detail->PreventiveRestructuring->LastDate);
            }

            if (!empty($detail->Liquidation)) {
                $response->Liquidation = $this->ParseLiquidationResult($detail->Liquidation);
            }

            if (!empty($detail->OtherProceeding)) {
                $response->OtherProceeding = $this->ParseProceeding($detail->OtherProceeding, new ProceedingResult());
            }

            if (!empty($detail->DistraintsAuthorizations)) {
                $response->DistraintsAuthorizations = $this->parseObjectArray($detail->DistraintsAuthorizations, 'DistraintsAuthorizationDetail', function($dad) {
                    $o = new DistraintsAuthorizationDetailResult();
                    $o->ReferenceNumber = (string) $dad->ReferenceNumber;
                    $o->Authorized = $this->parseObjectArray($dad->Authorized, 'BaseInfo', function($bi) {
                        $obi = new BaseInfo();
                        $obi->Name = (string)$bi->Name;
                        $obi->Ico = (string)$bi->Ico;
                        return $obi;
                    });
                    $o->TypeOfClaim = (string) $dad->TypeOfClaim;
                    $o->Plaintiff = (string) $dad->Plaintiff;
                    $o->PublishDate = $this->parseDate($dad->PublishDate);
                    $o->Url = (string) $dad->Url;
                    $o->Court = (string) $dad->Court;
                    $o->IdentifierNumber = (string) $dad->IdentifierNumber;
                    return $o;
                });
            }
        }

        return $response;
    }

    protected function ParseDebtResult($detail, $response = null)
    {
        if (!empty($detail)) {
            $o = (!empty($response)) ? $response : new DebtResult();
            $o->Source  = $detail->Source;
            $o->Value   = (float)$detail->Value;
            $o->ValidFrom  = $this->parseDate($detail->ValidFrom);
            return $o;
        }
        return null;
    }

    protected function ParseReceivableDebtResult($detail, $response = null)
    {
        if (!empty($detail)) {
            $o = (!empty($response)) ? $response : new ReceivableDebtResult();
            return $this->ParseDebtResult($detail, $o);
        }
        return null;
    }

    protected function ParseLiquidationResult($detail, $response = null)
    {
        if (!empty($detail)) {
            $o = (!empty($response)) ? $response : new LiquidationResult();
            $o->Source = (string) $detail->Source;
            $o->EnterDate = $this->parseDate($detail->EnterDate);
            $o->EnterReason = (string) $detail->EnterReason;
            $o->ExitDate = $this->parseDate($detail->ExitDate);
            if (!empty($detail->Officers)) {
                foreach ($detail->Officers->Officer as $officer) {
                    $of = $this->parsePerson($officer, new OfficerResult());
                    $of->Source  = (string)$officer->Source;
                    $o->Officers[] = $of;
                }
            }
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

    protected function ParseProceeding($detail, $response = null)
    {
        if (!empty($detail)) {
            $response = (!empty($response)) ? $response : new ProceedingResult();
            $o = $this->ParseLiquidationResult($detail, $response);
            if (!empty($o)) {
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
