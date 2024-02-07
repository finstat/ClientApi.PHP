<?php

require_once(__DIR__ . '/../FinStat.Client/Requests.php');
require_once(__DIR__ . '/../FinStat.Client/AbstractFinstatApi.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Reporting/ReportingResult.php');

class FinstatReportingApi extends AbstractFinstatApi
{
    public function RequestTopics($json = false)
    {
        $detail = $this->DoRequest("GetReportingTopics", array(), "reporting-topics", $json);
        if($detail != false) {
            if(!$json) {
                $result = array();
                foreach ($detail->ReportingTopic as $element) {       
                    $o = new ReportingTopic();
                    $o->ID      = (string)$element->ID;
                    $o->Name    = (string)$element->Name;
                    $o->Group   = (string)$element->Group;
                    $result[]   = $o;
                }
                return $result;
            } else {
                return $detail;
            }
        }

        return null;
    }

    public function RequestList($topic, $json = false)
    {
        $detail = $this->DoRequest("GetReportingList", array(
            'topic' => $topic,
        ), "reporting-list|" . $topic, $json);

        if($detail != false) {
            if(!$json) {
                $result = array();
                foreach ($detail->ReportOutput as $element) {
                    $o = new ReportOutput();
                    $o->FileName        = (string)$element->FileName;
                    $o->Description     = (string)$element->Description;
                    $o->Topic           = (string)$element->Topic;
                    $o->Group           = (string)$element->Group;
                    $o->Count           = (string)$element->Count;
                    $o->Date            = $this->parseDate($element->Date);
                    $result[]   = $o;
                }
                return $result;
            } else {
                return $detail;
            }
        }
        return null;
    }

    public function DownloadReportFile($fileID, $exportPath)
    {
        return $this->DownloadFile("GetReportingOutput", $fileID, $exportPath);
    }
}
