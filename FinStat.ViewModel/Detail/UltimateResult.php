<?php
require_once(__DIR__ . '/ExtendedResult.php');

class FunctionResult
{
    public
        $Type,
        $Description,
        $From
    ;
}

class AbstractPersonResult extends AddressResult
{
    public
        $FullName,
        $StructuredName,
        $DetectedFrom,
        $DetectedTo,
        $Functions = array()
    ;
}

class PersonResult extends AbstractPersonResult
{
    public
        $DepositAmount,
        $PaybackRange,
        $PartnersSharePercentage
    ;
}

class OfficerResult extends AbstractPersonResult
{
    public
        $Source
    ;
}

class RpvsPersonResult extends AbstractPersonResult
{
    public
        $BirthDate,
        $Ico
    ;
}

class RPOPersonResult
{
    public
        $BirthDate,
        $Citizenship,
        $FullName,
        $Country,
        $DetectedFrom,
        $DetectedTo,
        $StructuredName,
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
        $Officers = array(),
        $Source,
        $Deadlines = array()
    ;
}

class ProceedingResult extends LiquidationResult
{
    public
        $FileReference,
        $CourtCode,
        $StartDate,
        $ExitReason,
        $Status
    ;
}

class BankruptResult extends ProceedingResult
{
}

class RestructuringResult extends ProceedingResult
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
        $ORCancelled,
		$OtherProceeding,
        $RPOPersons
    ;
}
?>