<?php

require_once(__DIR__ . '/../FinStat.Client/Requests.php');
require_once(__DIR__ . '/../FinStat.Client/AbstractFinstatApi.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Deadline.php');
require_once(__DIR__ . '/../FinStat.ViewModel/BankuptcyRestructuing/BankruptcyRestructuring.php');

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
        $result = [];
        if (!empty($detail->BankruptcyRestructuring)) {
            foreach ($detail->BankruptcyRestructuring as $element) {
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
                $o->Debtors             = [];
                if (!empty($element->Debtors)) {
                    foreach ($element->Debtors->PersonAddress as $person) {
                        $p = $this->parsePersonAddress($person);
                        $o->Debtors[] = $p;
                    }
                }
                $o->Deadlines           = [];
                if (!empty($element->Deadlines)) {
                    foreach ($element->Deadlines->Deadline as $deadline) {
                        $d              = new Deadline();
                        $d->Type        = (string)$deadline->Type;
                        $d->Date        = empty($deadline->Date) ? null : new DateTime($deadline->Date);
                        $o->Deadlines[] = $d;
                    }
                }
                $result[] = $o;
            }
        }
        return $result;
    }
}
