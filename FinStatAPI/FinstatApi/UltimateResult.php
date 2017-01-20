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
        $Officer,
        $Source
    ;
}

class BankruptResult extends LiquidationResult
{
    public
        $StartDate,
        $ExitReason,
        $Status
    ;
}
class RestructuringResult extends BankruptResult
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
        $Bankrupt,
        $Restructuring,
        $Liquidation,
        $ORCancelled
    ;
}
?>