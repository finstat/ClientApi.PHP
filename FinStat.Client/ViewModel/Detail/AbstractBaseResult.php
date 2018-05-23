<?php
require_once(__DIR__ . '/../AddressResult.php');

class AbstractBaseResult extends AddressResult
{
    public
        $Ico,
        $IcDPH,
        $Activity,
        $Created,
        $Cancelled,
        $Url,
        $Warning,
        $WarningUrl
    ;
}