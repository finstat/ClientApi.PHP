<?php

abstract class AbstractStatementLegendResult
{
    public $Assets;
    public $LiabilitiesAndEquity;
}

class StatementLegendResult extends AbstractStatementLegendResult
{
    public $IncomeStatement;
}

class NonProfitStatementLegendResult extends AbstractStatementLegendResult
{
    public $Expenses;
    public $Revenue;
}

class StatementLegendValue
{
    public $ReportRow;
    public $ReportSection;
    public $Name;
}

class StatementItem
{
    public $Year;
    public $DateFrom;
    public $DateTo;
    public $DatePublished;
    public $Templates;
}

class StatementValue
{
    public $ReportRow;
    public $ReportSection;
    public $Actual;
    public $Previous;
}

class AssetStatementValue extends StatementValue
{
    public $ActualBrutto;
    public $ActualCorrection;
}

class FinancialStatementValue extends StatementValue
{
    public $ActualMain;
    public $ActualCommercial;
}

abstract class AbstractStatementResult
{
    public $ICO;
    public $Name;
    public $Year;
    public $DateFrom;
    public $DateTo;
    public $DatePublished;
    public $Format;
    public $OriginalFormat;
    public $Source;
    public $Assets;
    public $LiabilitiesAndEquity;
    public $PreviousAccountingPeriodFrom;
    public $PreviousAccountingPeriodTo;
}

class StatementResult extends AbstractStatementResult
{
    public $IncomeStatement;
}

class NonProfitStatementResult extends AbstractStatementResult
{
    public $Expenses;
    public $Revenue;
}
