<?php
require_once('../FinStat.Client/ViewModel/AddressResult.php');
require_once('../FinStat.Client/ViewModel/Detail/CommonResult.php');

class DetailResult extends CommonResult
{
    public
        $LegalForm,
        $OwnershipType,
        $EmployeeCount,
        $CzNaceCode,
        $CzNaceText,
        $CzNaceDicision,
        $CzNaceGroup
    ;
}
?>