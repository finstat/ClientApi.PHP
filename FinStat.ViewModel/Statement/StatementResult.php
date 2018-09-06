<?php

abstract class AbstractStatementLegendResult
{
    public
        $Assets,
        $LiabilitiesAndEquity
    ;
}

class StatementLegendResult extends AbstractStatementLegendResult
{
    public $IncomeStatement;
}

class NonProfitStatementLegendResult extends AbstractStatementLegendResult
{
    public
        $Expenses,
        $Revenue
    ;
}

class StatementLegendValue
{
    public
        $ReportRow,
        $ReportSection,
        $Name
    ;
}

class StatementItem
{
    public
        $Year,
        $DateFrom,
        $DateTo,
        $DatePublished,
        $Templates
    ;
}

class StatementValue
{
    public
        $ReportRow,
        $ReportSection,
        $Actual,
        $Previous
    ;
}

class AssetStatementValue extends StatementValue
{
    public
        $ActualBrutto,
        $ActualCorrection
    ;
}

class NonProfitAssetStatementValue extends StatementValue
{
    public
        $ActualMain,
        $ActualCommercial
    ;
}

abstract class AbstractStatementResult
{
    public
        $ICO,
        $Name,
        $Year,
        $DateFrom,
        $DateTo,
        $DatePublished,
        $Format,
        $OriginalFormat,
        $Source,
        $Assets,
        $LiabilitiesAndEquity
    ;
}

class StatementResult extends AbstractStatementResult
{
    public $IncomeStatement;
}

class NonProfitStatementResult extends AbstractStatementResult
{
    public
        $Expenses,
        $Revenue
    ;
}