<?php

class ProceedingResult
{
    public $DebtorsAddress;
    public $ProposersAddress;
    public $AdministratorsAddress;
    public $CourtsAddress;
    public $ReferenceFileNumber;
    public $Status;
    public $Character;
    public $EndReason;
    public $EndStatus;
    public $Url;
    public $Type;
    public $PublishDate;
    public $DatesInProceeding;
    public $FileIdentifierNumber;
    public $IssuedBy;
    public $PostedBy;
    public $Deadline;
}

class Address
{
    public $Name;
    public $Street;
    public $StreetNumber;
    public $ZipCode;
    public $City;
    public $Country;
    public $Region;
}

class PersonAddress extends Address
{
    public $BirthDate;
    public $Ico;
}

class AdministratorAddress extends PersonAddress
{
    public $Id;
}

class IssuedPerson
{
    public $Name;
    public $Function;
}

class Deadline
{
    public $Type;
    public $Date;
}
