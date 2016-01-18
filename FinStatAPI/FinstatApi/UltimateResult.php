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
        $DetectedFrom,
        $DetectedTo,
        $Functions = array()
    ;
}

class UltimateResult extends ExtendedResult
{
    public
        $ORSection, 
        $ORInsertNo,
        $Persons = array()
    ;
}
?>