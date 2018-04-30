<?php
require_once('../FinStat.Client/ViewModel/AddressResult.php');
require_once('../FinStat.Client/ViewModel/Detail/AbstractBaseResult.php');

class DetailResult extends AbstractBaseResult
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