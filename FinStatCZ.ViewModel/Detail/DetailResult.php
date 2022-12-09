<?php
namespace FinstatApiCz;

require_once(__DIR__ . '/../../FinStat.Client/ViewModel/AddressResult.php');
require_once(__DIR__ . '/../../FinStat.Client/ViewModel/Detail/CommonResult.php');

class DetailResult extends \CommonResult
{
    public
        $LegalForm,
        $OwnershipType,
        $EmployeeCount,
        $CzNaceCode,
        $CzNaceText,
        $CzNaceDivision,
        $CzNaceGroup
    ;
}
?>