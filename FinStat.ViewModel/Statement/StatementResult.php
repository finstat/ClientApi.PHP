<?php

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
        $Key,
        $Actual,
        $Previous
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
    public $Income;
}

class NonProfitStatementResult extends AbstractStatementResult
{
    public
        $Expenses,
        $Revenue
    ;
}