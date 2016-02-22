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

class PersonResult
{
    public
        $FullName,
        $Street,
        $StreetNumber,
        $ZipCode,
        $City,
        $Country,
        $Region,
        $District,
        $DetectedFrom,
        $DetectedTo,
        $Functions = array(),
        $DepositAmount,
        $PaybackRange
    ;
}

class CourtResult
{
    public
        $Name,
        $Street,
        $StreetNumber,
        $ZipCode,
        $City,
        $Country,
        $Region,
        $District
    ;
}

class UltimateResult extends ExtendedResult
{
    public
        $ORSection,
        $ORInsertNo,
        $Persons = array(),
        $BasicCapital,
        $PaybackRange,
        $RegistrationCourt
    ;
}
?>