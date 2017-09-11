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

class RpvsPersonResult extends AddressResult
{
    public
    $FullName,
    $BirthDate,
    $Ico,
    $DetectedFrom,
    $DetectedTo,
    $Functions = array()
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
        $Source,
        $Deadlines = array()
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

class DeadlineResult
{
    public
        $Type,
        $Date
    ;
}

class UltimateResult extends ExtendedResult
{
    public
        $EmployeesNumber,
        $ORSection,
        $ORInsertNo,
        $Persons = array(),
        $RpvsPersons = array(),
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