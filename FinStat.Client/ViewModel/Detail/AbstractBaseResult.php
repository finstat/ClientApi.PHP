<?php
require_once('../AddressResult.php');

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