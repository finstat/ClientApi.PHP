<?php

namespace FinstatApiCz;

require_once(__DIR__ . '/../../FinStat.Client/ViewModel/AddressResult.php');
require_once(__DIR__ . '/../../FinStat.Client/ViewModel/Detail/AbstractResult.php');

class DetailResult extends \CommonResult
{
    public $LegalForm;
    public $OwnershipType;
    public $EmployeeCount;
    public $CzNaceCode;
    public $CzNaceText;
    public $CzNaceDivision;
    public $CzNaceGroup;
}
