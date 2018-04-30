<?php
require_once('../FinStat.Client/Requests.php');
require_once('../FinStat.Client/AbstractFinstatApi.php');
require_once('../FinStat.Client/ViewModel/Monitoring/MonitoringReportResult.php');
require_once('../FinStat.ViewModel/Monitoring/ProceedingResult.php');

class FinstatMonitoringApi extends AbstractFinstatApi
{
    public function AddToMonitoring($ico, $json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'ico' => $ico,
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash($ico),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "AddToMonitoring";
        try
        {
            $headers = null;
            if ($json) {
                $url = $url . ".json";
            }
            $response = Requests::post($url, $headers, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }

        $detail = $this->parseResponse($response, $url, $ico, $json);

        $parse = (string)$detail;
        return ($json) ? $detail : ($parse == 'true');
    }

    public function AddDateToMonitoring($date, $json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'date' => $date,
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash($date),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "AddDateToMonitoring";
        try
        {
            $headers = null;
            if ($json) {
                $url = $url . ".json";
            }
            $response = Requests::post($url, $headers, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }

        $detail = $this->parseResponse($response, $url, $date, $json);

        $parse = (string)$detail;
        return ($json) ? $detail : ($parse == 'true');
    }

    public function RemoveFromMonitoring($ico, $json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'ico' => $ico,
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash($ico),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "RemoveFromMonitoring";
        try
        {
            $headers = null;
            if ($json) {
                $url = $url . ".json";
            }
            $response = Requests::post($url, $headers, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }

        $detail = $this->parseResponse($response, $url, $ico, $json);
        $parse = (string)$detail;

         return ($json) ? $detail : ($parse == 'true');
    }

    public function RemoveDateFromMonitoring($date, $json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'date' => $date,
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash($date),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "RemoveDateFromMonitoring";
        try
        {
            $headers = null;
            if ($json) {
                $url = $url . ".json";
            }
            $response = Requests::post($url, $headers, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }

        $detail = $this->parseResponse($response, $url, $date, $json);
        $parse = (string)$detail;

         return ($json) ? $detail : ($parse == 'true');
    }

    public function MonitoringList($json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash('list'),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "MonitoringList";
        try
        {
            $headers = null;
            if ($json) {
                $url = $url . ".json";
            }
            $response = Requests::post($url, $headers, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }
        $detail = $this->parseResponse($response, $url, null, $json);

        return ($json) ? $detail : $this->parseMonitoringList($detail);
    }

    public function MonitoringDateList($json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash('datelist'),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "MonitoringDateList";
        try
        {
            $headers = null;
            if ($json) {
                $url = $url . ".json";
            }
            $response = Requests::post($url, $headers, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }
        $detail = $this->parseResponse($response, $url, null, $json);

        return ($json) ? $detail : $this->parseMonitoringList($detail);
    }

    public function MonitoringReport($json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash('report'),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "MonitoringReport";
        try
        {
            $headers = null;
            if ($json) {
                $url = $url . ".json";
            }
            $response = Requests::post($url, $headers, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }

        $detail = $this->parseResponse($response, $url, null, $json);

        return ($json) ? $detail : $this->parseMonitoringReport($detail);
    }

    public function MonitoringDateReport($json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash('datereport'),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "MonitoringDateReport";
        try
        {
            $headers = null;
            if ($json) {
                $url = $url . ".json";
            }
            $response = Requests::post($url, $headers, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }

        $detail = $this->parseResponse($response, $url, null, $json);
        return ($json) ? $detail : $this->parseMonitoringDateReport($detail);
    }

    public function MonitoringProceedings($json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash('proceedings'),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "MonitoringProceedings";
        try
        {
            $headers = null;
            if ($json) {
                $url = $url . ".json";
            }
            $response = Requests::post($url, $headers, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }
        $detail = $this->parseResponse($response, $url, null, $json);
        return ($json) ? $detail : $this->parseMonitoringProceedings($detail);
    }

    public function MonitoringDateProceedings($json = false)
    {
        $options = $this->InitRequests();

        $data = array(
            'apiKey' => $this->apiKey,
            'Hash' => $this->ComputeVerificationHash('dateproceedings'),
            'StationId' => $this->stationId,
            'StationName' => $this->stationName
        );

        $url = $this->apiUrl . "MonitoringDateProceedings";
        try
        {
            $headers = null;
            if ($json) {
                $url = $url . ".json";
            }
            $response = Requests::post($url, $headers, $data, $options);
        }
        catch(Requests_Exception $e)
        {
            throw $e;
        }
        $detail = $this->parseResponse($response, $url, null, $json);
        return ($json) ? $detail : $this->parseMonitoringProceedings($detail);
    }

    private function parseMonitoringList($detail)
    {
        if  ($detail === FALSE) {
            return $detail;
        }

        $response =  array();
        if (!empty($detail->string)) {
            foreach ($detail->string as $s) {
                $response[] = (string) $s;
            }
        }

        return $response;
    }

    private function parseMonitoringReport($detail)
    {
        if  ($detail === FALSE) {
            return $detail;
        }

        $response =  array();
        if (!empty($detail->Monitoring)) {
            foreach ($detail->Monitoring as $element) {
                $o = new MonitoringReportResult();
                $o->Ident        = (string)$element->Ident;
                $o->Ico          = (string)$element->Ico;
                $o->Name         = (string)$element->Name;
                $o->PublishDate  = empty($element->PublishDate) ? null : new DateTime($element->PublishDate);
                $o->Type         = (string)$element->Type;
                $o->Description  = (string)$element->Description;
                $o->Url          = (string)$element->Url;
                $response[] = $o;
            }
        }

        return $response;
    }

    private function parseMonitoringDateReport($detail)
    {
        if  ($detail === FALSE) {
            return $detail;
        }

        $response =  array();
        if (!empty($detail->MonitoringDate)) {
            foreach ($detail->MonitoringDate as $element) {
                $o = new MonitoringDateReportResult();
                $o->Ident        = (string)$element->Ident;
                $o->Date         = (string)$element->Date;
                $o->Name         = (string)$element->Name;
                $o->PublishDate  = empty($element->PublishDate) ? null : new DateTime($element->PublishDate);
                $o->Type         = (string)$element->Type;
                $o->Description  = (string)$element->Description;
                $o->Url          = (string)$element->Url;
                $response[] = $o;
            }
        }

        return $response;
    }

    private function parseMonitoringProceedings($detail)
    {
        if  ($detail === FALSE) {
            return $detail;
        }

        $response =  array();
        if (!empty($detail->ProceedingResult)) {
            foreach ($detail->ProceedingResult as $element) {
                $o = new ProceedingResult();
                if(!empty($element->DebtorsAddress)) {
                    $array = array();
                    foreach ($element->DebtorsAddress->PersonAddress as $address) {
                        $array[] = $this->parsePersonAddress($address);
                    }
                    $o->DebtorsAddress = $array;
                }
                if(!empty($element->ProposersAddress)) {
                    $array = array();
                    foreach ($element->ProposersAddress->PersonAddress as $address) {
                        $array[] = $this->parsePersonAddress($address);
                    }
                    $o->ProposersAddress = $array;
                }
                if(!empty($element->AdministratorsAddress)) {
                    $array = array();
                    foreach ($element->AdministratorsAddress->PersonAddress as $address) {
                         $array[] = $this->parsePersonAddress($address);
                    }
                    $o->AdministratorsAddress = $array;
                }
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

                if (!empty($element->FileIdentifierNumber)) {
                    $array  = array();
                    foreach ($element->FileIdentifierNumber->string as $s) {
                        $array[] = (string) $s;
                    }
                    $o->FileIdentifierNumber = $array;
                }

                if(!empty($element->IssuedBy)) {
                    $p = new IssuedPerson();
                    $p->Name        = (string)$element->Name;
                    $p->Function    = (string)$element->Function;
                    $o->IssuedBy  = $p;
                }

                if(!empty($element->DatesInProceeding)) {
                    $array  = array();
                    foreach ($element->DatesInProceeding->Deadline as $deadline) {
                         $d = new Deadline();
                         $d->Type        = (string)$deadline->Type;
                         $d->Date        = empty($deadline->Date) ? null : new DateTime($deadline->Date);
                         $array[] = $d;
                    }
                }
                $response[] = $array;
            }
        }

        return $response;
    }
}
?>