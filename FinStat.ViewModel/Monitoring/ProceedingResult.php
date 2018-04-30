<?php

class ProceedingResult
{
    public
        $DebtorsAddress,
        $ProposersAddress,
        $AdministratorsAddress,
        $CourtsAddress,
        $ReferenceFileNumber,
        $Status,
        $Character,
        $EndReason,
        $EndStatus,
        $Url,
        $Type,
        $PublishDate,
        $DatesInProceeding,
        $FileIdentifierNumber,
        $IssuedBy,
        $PostedBy;
}

class Address
{
    public
        $Name,
        $Street,
        $StreetNumber,
        $ZipCode,
        $City,
        $Country,
        $Region;
}

class PersonAddress extends Address
{
    public
        $BirthDate,
        $Ico;
}

class IssuedPerson
{
    public
        $Name,
        $Function;
}

class Deadline
{
    public
        $Type,
        $Date;
}
?>