<?php

require_once(__DIR__ . '/ExtendedResult.php');

class FunctionResult
{
    public $Type;
    public $Description;
    public $From;
}

class AbstractPersonResult extends AddressResult
{
    public $FullName;
    public $StructuredName;
    public $DetectedFrom;
    public $DetectedTo;
    public $Functions = array();
}

class PersonResult extends AbstractPersonResult
{
    public $DepositAmount;
    public $PaybackRange;
    public $PartnersSharePercentage;
}

class OfficerResult extends AbstractPersonResult
{
    public $Source;
}

class RpvsPersonResult extends AbstractPersonResult
{
    public $BirthDate;
    public $Ico;
}

class RPOPersonResult
{
    public $BirthDate;
    public $Citizenship;
    public $FullName;
    public $Country;
    public $DetectedFrom;
    public $DetectedTo;
    public $StructuredName;
    public $Functions = array();
}

class CourtResult extends AddressResult
{
    public $Name;
}

class HistoryAddressResult extends AddressResult
{
    public $ValidFrom;
    public $ValidTo;
}

class LiquidationResult
{
    public $EnterDate;
    public $EnterReason;
    public $ExitDate;
    public $Officers = array();
    public $Source;
    public $Deadlines = array();
}

class ProceedingResult extends LiquidationResult
{
    public $FileReference;
    public $CourtCode;
    public $StartDate;
    public $ExitReason;
    public $Status;
}

class BankruptResult extends ProceedingResult
{
}

class RestructuringResult extends ProceedingResult
{
}

class DeadlineResult
{
    public $Type;
    public $Date;
}

class DistraintsAuthorizationDetailResult
{
    public $ReferenceNumber;
    public $Authorized;
    public $TypeOfClaim;
    public $Plaintiff;
    public $PublishDate;
    public $Url;
    public $Court;
    public $IdentifierNumber;
}

class UltimateResult extends ExtendedResult
{
    public $EmployeesNumber;
    public $ORSection;
    public $ORInsertNo;
    public $Persons = array();
    public $RpvsPersons = array();
    public $PaybackRange;
    public $RegistrationCourt;
    public $WebPages;
    public $AddressHistory;
    public $StatutoryAction;
    public $ProcurationAction;
    public $Bankrupt;
    public $Restructuring;
    public $Liquidation;
    public $ORCancelled;
    public $OtherProceeding;
    public $RPOPersons;
    public $DistraintsAuthorizations;
}
