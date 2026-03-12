<?php

namespace FinStat\Api;

use FinStat\Client\AbstractFinstatApi;
use FinStat\ViewModel\Reporting\ReportingResult;
use FinStat\ViewModel\Reporting\ReportingTopic;

class FinstatReportingApi extends AbstractFinstatApi
{
    public function RequestTopics($json = false)
    {
        $detail = $this->DoRequest("GetReportingTopics", array(), "reporting-topics", $json);
        if($detail != false) {
            if(!$json) {
                return $this->parseObjectArray($detail, 'ReportingTopic', function($element) {
                    $o = new ReportingTopic();
                    $o->ID      = (string)$element->ID;
                    $o->Name    = (string)$element->Name;
                    $o->Group   = (string)$element->Group;
                    return $o;
                });
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
                return $this->parseObjectArray($detail, 'ReportOutput', function($element) {
                    $o = new ReportOutput();
                    $o->FileName        = (string)$element->FileName;
                    $o->Description     = (string)$element->Description;
                    $o->Topic           = (string)$element->Topic;
                    $o->Group           = (string)$element->Group;
                    $o->Count           = (string)$element->Count;
                    $o->Date            = $this->parseDate($element->Date);
                    return $o;
                });
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
