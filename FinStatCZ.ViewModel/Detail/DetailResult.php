<?php

require_once('../FinStat.Client/ViewModel/AddressResult.php');
require_once('../FinStat.Client/ViewModel/Detail/CommonResult.php');

class DetailResult extends CommonResult
{
    public $LegalForm;
    public $OwnershipType;
    public $EmployeeCount;
    public $CzNaceCode;
    public $CzNaceText;
    public $CzNaceDicision;
    public $CzNaceGroup;
}
