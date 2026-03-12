<?php

namespace FinStat\Api;

use FinStat\Client\AbstractFinstatApi;
use FinStat\ViewModel\Deadline;
use FinStat\ViewModel\BankuptcyRestructuing\BankruptcyRestructuring;
use DateTime;

class FinstatBankruptcyRestructuringApi extends AbstractFinstatApi
{
    public function RequestPersonBankruptcyProceedings($name, $surname, $dateOfBirth, $json = false)
    {
        $detail = $this->DoRequest("/PersonBankruptcyProceedings", array(
            'name' => $name,
            'surname' => $surname,
            'dateOfBirth' => $dateOfBirth->format('Y-m-d')
        ), $name . "|" . $surname . "|" . $dateOfBirth->format('Y-m-d'), $json);

        if($detail != false) {
            if(!$json) {
                $result = [];
                return $this->parseBankruptcyRestructuringList($detail);
            } else {
                return $detail;
            }
        }

        return null;
    }

    public function RequestCompanyBankruptcyRestructuring($ico = null, $name = null, $json = false)
    {
        $detail = $this->DoRequest("/CompanyBankruptcyRestructuring", array(
            'ico' => $ico,
            'name' => $name,
        ), $ico . $name, $json);

        if($detail != false) {
            if(!$json) {
                return $this->parseBankruptcyRestructuringList($detail);
            } else {
                return $detail;
            }
        }

        return null;
    }

    private function parseBankruptcyRestructuringList($detail)
    {
        return $this->parseObjectArray($detail, 'BankruptcyRestructuring', function($element) {
            $o = new BankruptcyRestructuring();
            $o->FileReference       = (string)$element->FileReference;
            $o->FirstRecordDate     = empty($element->FirstRecordDate) ? null : new DateTime($element->FirstRecordDate);
            $o->LastRecordDate      = empty($element->LastRecordDate) ? null : new DateTime($element->LastRecordDate);
            $o->RUState             = (string)$element->RUState;
            $o->RUStateDate         = empty($element->RUStateDate) ? null : new DateTime($element->RUStateDate);
            $o->OVState             = (string)$element->OVState;
            $o->OVStateDate         = empty($element->OVStateDate) ? null : new DateTime($element->OVStateDate);
            $o->EnterDate           = empty($element->EnterDate) ? null : new DateTime($element->EnterDate);
            $o->ExitDate            = empty($element->ExitDate) ? null : new DateTime($element->ExitDate);
            $o->EndState            = (string)$element->EndState;
            $o->EndReason           = (string)$element->EndReason;       
            $o->FinstatURL          = (string)$element->FinstatURL;
            $o->Debtors = $this->parseObjectArray($element->Debtors, 'PersonAddress', function($person) {
                return $this->parsePersonAddress($person);
            });
            $o->Deadlines = $this->parseObjectArray($element->Deadlines, 'Deadline', function($deadline) {
                $d              = new Deadline();
                $d->Type        = (string)$deadline->Type;
                $d->Date        = empty($deadline->Date) ? null : new DateTime($deadline->Date);
                return $d;
            });
            return $o;
        });
    }
}
