<?php

namespace FinStat\Api;

use FinStat\Client\AbstractFinstatApi;
use FinStat\Client\ViewModel\Monitoring\MonitoringReportResult;
use FinStat\Client\ViewModel\Monitoring\MonitoringDateReportResult;
use FinStat\Client\ViewModel\Monitoring\MonitoringCategory;
use FinStat\ViewModel\Deadline;
use FinStat\ViewModel\Monitoring\ProceedingResult;

class FinstatMonitoringApi extends AbstractFinstatApi
{
    public function AddToMonitoring($ico, $category = null, $json = false)
    {
        $detail = $this->DoRequest("AddToMonitoring", array('ico' => $ico, 'category' => $category), $ico, $json);

        return ($json) ? $detail : $this->parseBoolean($detail);
    }

    public function RemoveFromMonitoring($ico, $category = null, $json = false)
    {
        $detail = $this->DoRequest("RemoveFromMonitoring", array('ico' => $ico, 'category' => $category), $ico, $json);

        return ($json) ? $detail : $this->parseBoolean($detail);
    }

    public function MonitoringList($category = null, $json = false)
    {
        $detail = $this->DoRequest("MonitoringList", array('category' => $category), "list", $json);

        return ($json) ? $detail : $this->parseMonitoringList($detail);
    }

    public function MonitoringReport($category = null, $json = false)
    {
        $detail = $this->DoRequest("MonitoringReport", array('category' => $category), "report", $json);

        return ($json) ? $detail : $this->parseMonitoringReport($detail);
    }

    public function MonitoringProceedings($json = false)
    {
        $detail = $this->DoRequest("MonitoringProceedings", array(), "proceedings", $json);

        return ($json) ? $detail : $this->parseMonitoringProceedings($detail);
    }

    public function AddDateToMonitoring($date, $category = null, $json = false)
    {
        $detail = $this->DoRequest("AddDateToMonitoring", array('date' => $date, 'category' => $category), $date, $json);

        return ($json) ? $detail : $this->parseBoolean($detail);
    }

    public function RemoveDateFromMonitoring($date, $category = null, $json = false)
    {
        $detail = $this->DoRequest("RemoveDateFromMonitoring", array('date' => $date, 'category' => $category), $date, $json);

        return ($json) ? $detail : $this->parseBoolean($detail);
    }

    public function MonitoringDateList($category = null, $json = false)
    {
        $detail = $this->DoRequest("MonitoringDateList", array('category' => $category), "datelist", $json);

        return ($json) ? $detail : $this->parseMonitoringList($detail);
    }

    public function MonitoringDateReport($category = null, $json = false)
    {
        $detail = $this->DoRequest("MonitoringDateReport", array('category' => $category), "datereport", $json);

        return ($json) ? $detail : $this->parseMonitoringDateReport($detail);
    }

    public function MonitoringDateProceedings($json = false)
    {
        $detail = $this->DoRequest("MonitoringDateProceedings", array(), "dateproceedings", $json);

        return ($json) ? $detail : $this->parseMonitoringProceedings($detail);
    }

    private function parseMonitoringList($detail)
    {
        if  ($detail === false) {
            return $detail;
        }

        return $this->parseStringArray($detail);
    }

    private function parseMonitoringReport($detail)
    {
        if  ($detail === false) {
            return $detail;
        }

        return $this->parseObjectArray($detail, 'Monitoring', function($element) {
            $o = new MonitoringReportResult();
            $o->Ident        = (string)$element->Ident;
            $o->Ico          = (string)$element->Ico;
            $o->Name         = (string)$element->Name;
            $o->PublishDate  = empty($element->PublishDate) ? null : new DateTime($element->PublishDate);
            $o->Type         = (string)$element->Type;
            $o->Description  = (string)$element->Description;
            $o->Categories   = $this->parseStringArray($element->Categories);
            return $o;
        });
    }

    private function parseMonitoringDateReport($detail)
    {
        if  ($detail === false) {
            return $detail;
        }

        return $this->parseObjectArray($detail, 'MonitoringDate', function($element) {
            $o = new MonitoringDateReportResult();
            $o->Ident        = (string)$element->Ident;
            $o->Date         = (string)$element->Date;
            $o->Name         = (string)$element->Name;
            $o->PublishDate  = empty($element->PublishDate) ? null : new DateTime($element->PublishDate);
            $o->Type         = (string)$element->Type;
            $o->Description  = (string)$element->Description;
            $o->Url          = (string)$element->Url;
            return $o;
        });
    }

    public function parseAdministratorAddress($address)
    {
        $element = $this->parsePersonAddress($address, new AdministratorAddress());
        $element->Id =  (string)$address->Id;
        return $element;
    }

    private function parseMonitoringProceedings($detail)
    {
        if  ($detail === false) {
            return $detail;
        }

        return $this->parseObjectArray($detail, 'ProceedingResult', function($element) {
            $o = new MonitoringProceedingResult();
            $o->DebtorsAddress = $this->parseObjectArray($element->DebtorsAddress, 'PersonAddress', function($address) {
                return $this->parsePersonAddress($address);
            });
            $o->ProposersAddress = $this->parseObjectArray($element->ProposersAddress, 'PersonAddress', function($address) {
                return $this->parsePersonAddress($address);
            });
            $o->AdministratorsAddress = $this->parseObjectArray($element->AdministratorsAddress, 'AdministratorAddress', function($address) {
                return $this->parseAdministratorAddress($address);
            });
            if(!empty($element->CourtsAddress)) {
                $o->CourtsAddress  = $this->parseFullAddress($element->CourtsAddress);
            }
            $o->ReferenceFileNumber     = (string)$element->ReferenceFileNumber;
            $o->Status                  = (string)$element->Status;
            $o->Character               = (string)$element->Character;
            $o->EndReason               = (string)$element->EndReason;
            $o->EndStatus               = (string)$element->EndStatus;
            $o->Url                     = (string)$element->Url;
            $o->Type                    = (string)$element->Type;
            $o->PublishDate             = empty($element->PublishDate) ? null : new DateTime($element->PublishDate);
            $o->Deadline                = empty($element->Deadline) ? null : new DateTime($element->Deadline);
            $o->PostedBy                = (string)$element->PostedBy;

            $o->FileIdentifierNumber = $this->parseStringArray($element->FileIdentifierNumber);

            if(!empty($element->IssuedBy)) {
                $p = new IssuedPerson();
                $p->Name        = (string)$element->Name;
                $p->Function    = (string)$element->Function;
                $o->IssuedBy  = $p;
            }

            if(!empty($element->DatesInProceeding)) {
                $o->DatesInProceeding = $this->parseObjectArray($element->DatesInProceeding, 'Deadline', function($deadline) {
                    $d = new Deadline();
                    $d->Type        = (string)$deadline->Type;
                    $d->Date        = empty($deadline->Date) ? null : new DateTime($deadline->Date);
                    return $d;
                });
            }
            return $o;
        });
    }
}
