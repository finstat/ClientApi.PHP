<?php
require_once('ExtendedResult.php');

class FunctionResult
{
    public
        $Type,
        $Description,
        $From
    ;
}

class PersonResult extends AddressResult
{
    public
        $FullName,
        $DetectedFrom,
        $DetectedTo,
        $Functions = array(),
        $DepositAmount,
        $PaybackRange
    ;
}

class CourtResult extends AddressResult
{
    public
        $Name
    ;
}

class HistoryAddressResult extends AddressResult
{
    public
        $ValidFrom,
        $ValidTo
    ;
}

class LiquidationResult
{
    public
        $EnterDate,
        $EnterReason,
        $ExitDate,
        $Officer
    ;
}

class TenderResult extends LiquidationResult
{
    public
        $ExitReason
    ;
}
class RestructuringResult extends TenderResult
{
}

class UltimateResult extends ExtendedResult
{
    public
        $ORSection,
        $ORInsertNo,
        $Persons = array(),
        $BasicCapital,
        $PaybackRange,
        $RegistrationCourt,
        $WebPages,
        $AddressHistory,
        $StatutoryAction,
        $ProcurationAction,
        $LastTender,
        $LastRestructuring,
        $LastLiquidation,
        $ORCancelled
    ;
}
?>