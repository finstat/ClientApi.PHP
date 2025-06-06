<?php

class BaseInfo
{
    public $Name;
    public $Ico;
}

class AddressResult
{
    public $Name;
    public $Street;
    public $StreetNumber;
    public $ZipCode;
    public $City;
    public $District;
    public $Region;
    public $Country;
}

class PersonAddressResult extends AddressResult
{
    public $Ico;
    public $BirthDate;
}
