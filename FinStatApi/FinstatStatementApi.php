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

                $result->LiabilitiesAndEquity = array();
                foreach ($detail->LiabilitiesAndEquity as $element) {
                    $o = new StatementValue();
                    $o->Row    =  (string)$element->Row;
                    $o->Section    =  (string)$element->Section;
                    $o->Actual =  (float)$element->Actual;
                    $o->Previous    =  (float)$element->Previous;
                    $result->LiabilitiesAndEquity[] = $o;
                }
                if ($isNonProfit) {
                    $result->Assets = array();
                    foreach ($detail->Assets as $element) {
                        $o = new AssetStatementValue();
                        $o->Row    =  (string)$element->Row;
                        $o->Section    =  (string)$element->Section;
                        $o->Actual =  (float)$element->Actual;
                        $o->Previous    =  (float)$element->Previous;
                        $o->ActualBrutto =  (float)$element->ActualBrutto;
                        $o->ActualCorrection    =  (float)$element->ActualCorrection;
                        $result->Assets[] = $o;
                    }

                    $result->Expenses = array();
                    foreach ($detail->Expenses as $element) {
                        $o = new FinanceStatementValue();
                        $o->Row    =  (string)$element->Row;
                        $o->Section    =  (string)$element->Section;
                        $o->Actual =  (float)$element->Actual;
                        $o->Previous    =  (float)$element->Previous;
                        $o->ActualMain =  (float)$element->ActualMain;
                        $o->ActualCommercial    =  (float)$element->ActualCommercial;
                        $result->Expenses[] = $o;
                    }

                    $result->Revenue = array();
                    foreach ($detail->Revenue as $element) {
                        $o = new FinanceStatementValue();
                        $o->Row    =  (string)$element->Row;
                        $o->Section    =  (string)$element->Section;
                        $o->Actual =  (float)$element->Actual;
                        $o->Previous    =  (float)$element->Previous;
                        $o->ActualMain =  (float)$element->ActualMain;
                        $o->ActualCommercial    =  (float)$element->ActualCommercial;
                        $result->Revenue[] = $o;
                    }
                } else {
                    $result->Assets = array();
                    foreach ($detail->Assets as $element) {
                        $o = new AssetStatementValue();
                        $o->Row    =  (string)$element->Row;
                        $o->Section    =  (string)$element->Section;
                        $o->Actual =  (float)$element->Actual;
                        $o->Previous    =  (float)$element->Previous;
                        $o->ActualBrutto =  (float)$element->ActualBrutto;
                        $o->ActualCorrection    =  (float)$element->ActualCorrection;
                        $result->Assets[] = $o;
                    }

                    $result->IncomeStatement = array();
                    foreach ($detail->IncomeStatement as $element) {
                        $o = new StatementValue();
                        $o->Row    =  (string)$element->Row;
                        $o->Section    =  (string)$element->Section;
                        $o->Actual =  (float)$element->Actual;
                        $o->Previous    =  (float)$element->Previous;
                        $result->IncomeStatement[] = $o;
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
        $detail = $this->DoRequest("GetStatementTemplateLegend", array(
            'lang' => $lang,
            'template' => $template
        ), $lang, $json);

        if($detail != false)
        {
            if(!$json)
            {
                $isNonProfit = ($template == "TemplateNujPU" || $template == "TemplateNujPU");
                $result = $isNonProfit ? new NonProfitStatementLegendResult() : new StatementLegendResult();

                $result->Assets = array();
                foreach ($detail->Assets as $element) {
                    $o = new StatementLegendValue();
                    $o->ReportRow = (string)$element->Row;
                    $o->ReportSection = (string)$element->Section;
                    $o->Name = (string)$element->Name;
                    $result->Assets[] = $o;
                }

                $result->LiabilitiesAndEquity = array();
                foreach ($detail->LiabilitiesAndEquity as $element) {
                    $o = new StatementLegendValue();
                    $o->ReportRow = (string)$element->Row;
                    $o->ReportSection = (string)$element->Section;
                    $o->Name = (string)$element->Name;
                    $result->LiabilitiesAndEquity[] = $o;
                }

                if ($isNonProfit) {
                    $result->Expenses = array();
                    foreach ($detail->Expenses as $element) {
                        $o = new StatementLegendValue();
                        $o->ReportRow = (string)$element->Row;
                        $o->ReportSection = (string)$element->Section;
                        $o->Name = (string)$element->Name;
                        $result->Expenses[] = $o;
                    }

                    $result->Revenue = array();
                    foreach ($detail->Revenue as $element) {
                        $o = new StatementLegendValue();
                        $o->ReportRow = (string)$element->Row;
                        $o->ReportSection = (string)$element->Section;
                        $o->Name = (string)$element->Name;
                        $result->Revenue[] = $o;
                    }
                } else {
                    $result->IncomeStatement = array();
                    foreach ($detail->IncomeStatement as $element) {
                        $o = new StatementLegendValue();
                        $o->ReportRow = (string)$element->Row;
                        $o->ReportSection = (string)$element->Section;
                        $o->Name = (string)$element->Name;
                        $result->IncomeStatement[] = $o;
                    }
                }

                return $result;
            } else {
                return $detail;
            }
        }

        return null;
    }
}
