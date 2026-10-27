<?php

namespace FinStat\Api;

use FinStat\Client\AbstractFinstatApi;
use FinStat\ViewModel\KeyValue;
use FinStat\ViewModel\Statement\StatementResult;
use FinStat\ViewModel\Statement\StatementItem;
use FinStat\ViewModel\Statement\StatementValue;
use FinStat\ViewModel\Statement\AssetStatementValue;
use FinStat\ViewModel\Statement\FinancialStatementValue;
use FinStat\ViewModel\Statement\NonProfitStatementResult;
use FinStat\ViewModel\Statement\StatementLegendResult;
use FinStat\ViewModel\Statement\NonProfitStatementLegendResult;
use FinStat\ViewModel\Statement\StatementLegendValue;

class FinstatStatementApi extends AbstractFinstatApi
{
    public function RequestStatements($ico, $json = false)
    {
        $detail = $this->DoRequest("GetStatements", array(
            'ico' => $ico,
        ), $ico, $json);

        if ($detail !== false) {
            if(!$json) {
                $result = array();
                foreach ($detail->StatementItem as $element) {
                    $o = new StatementItem();
                    $o->Year = (int)$element->Year;
                    $o->DateFrom = $this->parseDate($element->DateFrom);
                    $o->DateTo = $this->parseDate($element->DateTo);
                    $o->DatePublished = $this->parseDate($element->DatePublished);
                    $o->Templates =  array();
                    if (!empty($element->Templates)) {
                        foreach ($element->Templates->TemplateTypeEnum as $s) {
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

        if ($detail !== false) {
            if(!$json) {
                $isNonProfit = ($template == "TemplateNujPU" || $template == "TemplateROPO");
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

                $result->Assets = $this->parseObjectArray($detail->Assets, 'AssetStatementValue', function ($element) {
                    $o = $this->parseStatementValue($element, new AssetStatementValue());
                    $o->ActualBrutto = $this->parseNullableFloat($element->ActualBrutto);
                    $o->ActualCorrection = $this->parseNullableFloat($element->ActualCorrection);
                    return $o;
                });
                $result->LiabilitiesAndEquity = $this->parseObjectArray($detail->LiabilitiesAndEquity, 'StatementValue', function ($element) {
                    return $this->parseStatementValue($element, new StatementValue());
                });
                if ($isNonProfit) {
                    $financial = function ($element) {
                        $o = $this->parseStatementValue($element, new FinancialStatementValue());
                        $o->ActualMain = $this->parseNullableFloat($element->ActualMain);
                        $o->ActualCommercial = $this->parseNullableFloat($element->ActualCommercial);
                        return $o;
                    };
                    $result->Expenses = $this->parseObjectArray($detail->Expenses, 'FinancialStatementValue', $financial);
                    $result->Revenue = $this->parseObjectArray($detail->Revenue, 'FinancialStatementValue', $financial);
                } else {
                    $result->IncomeStatement = $this->parseObjectArray($detail->IncomeStatement, 'StatementValue', function ($element) {
                        return $this->parseStatementValue($element, new StatementValue());
                    });
                }

                $result->PreviousAccountingPeriodFrom = $this->parseDate($detail->PreviousAccountingPeriodFrom);
                $result->PreviousAccountingPeriodTo = $this->parseDate($detail->PreviousAccountingPeriodTo);

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

        if ($detail !== false) {
            if(!$json) {
                $isNonProfit = ($template == "TemplateNujPU" || $template == "TemplateROPO");
                $result = $isNonProfit ? new NonProfitStatementLegendResult() : new StatementLegendResult();

                $result->Assets = $this->parseStatementLegendValues($detail->Assets);
                $result->LiabilitiesAndEquity = $this->parseStatementLegendValues($detail->LiabilitiesAndEquity);
                if ($isNonProfit) {
                    $result->Expenses = $this->parseStatementLegendValues($detail->Expenses);
                    $result->Revenue = $this->parseStatementLegendValues($detail->Revenue);
                } else {
                    $result->IncomeStatement = $this->parseStatementLegendValues($detail->IncomeStatement);
                }

                return $result;
            } else {
                return $detail;
            }
        }

        return null;
    }

    private function parseStatementValue($element, StatementValue $o): StatementValue
    {
        $o->ReportRow = (string)$element->ReportRow;
        $o->ReportSection = (string)$element->ReportSection;
        $o->Actual = $this->parseNullableFloat($element->Actual);
        $o->Previous = $this->parseNullableFloat($element->Previous);
        return $o;
    }
}
