<?php
require_once(__DIR__ . '/../FinStat.Client/Requests.php');
require_once(__DIR__ . '/../FinStat.Client/AbstractFinstatApi.php');
require_once(__DIR__ . '/../FinStat.ViewModel/KeyValue.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Statement/StatementResult.php');

class FinstatStatementApi extends AbstractFinstatApi
{

    public function RequestStatements($ico, $json = false)
    {
        $detail = $this->DoRequest("GetStatements", array(
            'ico' => $ico,
        ), $ico, $json);

        if($detail != false)
        {
            if(!$json)
            {
                $result = array();
                foreach ($detail->StatementItem as $element) {
                    $o = new StatementItem();
                    $o->Year = (int)$element->Year;
                    $o->DateFrom = $this->parseDate($element->DateFrom);
                    $o->DateTo = $this->parseDate($element->DateTo);
                    $o->DatePublished = $this->parseDate($element->DatePublished);
                    $o->Templates =  array();
                    if (!empty($detail->Templates)) {
                        foreach ($detail->Templates->TemplateTypeEnum as $s) {
                            $o->Templates[] = (string)$s;
                        }
                    }
                    $result[] = $o;
                }
                return $result;
            } else {
                return $detail;
            }
        }

        return null;
    }

    public function RequestStatementDetail($ico, $year, $template, $json = false)
    {
        $detail = $this->DoRequest("GetStatementDetail", array(
            'ico' => $ico,
            'year' => $year,
            'template' => $template,
        ), $ico . "|" . $year, $json);

        if($detail != false)
        {
            if(!$json)
            {
                $isNonProfit = ($template == "TemplateNujPU" || $template == "TemplateNujPU");
                $result = $isNonProfit ? new NonProfitStatementResult() : new StatementResult();

                $result->ICO = (string)$detail->ICO;
                $result->Name = (string)$detail->Name;
                $result->Year = (int)$detail->Year;
                $result->DateFrom = $this->parseDate($detail->DateFrom);
                $result->DateTo = $this->parseDate($detail->DateTo);
                $result->DatePublished = $this->parseDate($detail->DatePublished);
                $result->Format = (string)$detail->Format;
                $result->OriginalFormat = (string)$detail->OriginalFormat;
                $result->Source = (string)$detail->Source;

                foreach ($detail->Assets as $element) {
                    $o = new StatementValue();
                    $o->Key    =  (string)$element->Key;
                    $o->Actual =  (float)$element->Actual;
                    $o->Previous    =  (float)$element->Previous;
                    $result>Assets[] = $o;
                }

                foreach ($detail->LiabilitiesAndEquity as $element) {
                    $o = new StatementValue();
                    $o->Key    =  (string)$element->Key;
                    $o->Actual =  (float)$element->Actual;
                    $o->Previous    =  (float)$element->Previous;
                    $result->LiabilitiesAndEquity[] = $o;
                }

                if($isNonProfit) {
                    foreach ($detail->Expenses as $element) {
                        $o = new StatementValue();
                        $o->Key    =  (string)$element->Key;
                        $o->Actual =  (float)$element->Actual;
                        $o->Previous    =  (float)$element->Previous;
                        $result->Expenses[] = $o;
                    }

                    foreach ($detail->Revenue as $element) {
                        $o = new StatementValue();
                        $o->Key    =  (string)$element->Key;
                        $o->Actual =  (float)$element->Actual;
                        $o->Previous    =  (float)$element->Previous;
                        $result->Revenue[] = $o;
                    }
                } else {
                    foreach ($detail->Income as $element) {
                        $o = new StatementValue();
                        $o->Key    =  (string)$element->Key;
                        $o->Actual =  (float)$element->Actual;
                        $o->Previous    =  (float)$element->Previous;
                        $result->Income[] = $o;
                    }
                }

                return $result;
            } else {
                return $detail;
            }
        }

        return null;
    }

    public function RequestStatementLegend($template, $lang = "sk", $json = false)
    {
        $detail = $this->DoRequest("GetStatementLegend", array(
            'lang' => $lang,
            'template' => $template
        ), $lang, $json);

        if($detail != false)
        {
            if(!$json)
            {
                $result = array();
                foreach ($detail->KeyValue as $element) {
                    $o = new KeyValue();
                    $o->Key = (string)$element->Key;
                    $o->Value = (string)$element->Value;
                    $result[] = $o;
                }

                return $result;
            } else {
                return $detail;
            }
        }

        return null;
    }
}
