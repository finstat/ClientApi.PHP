<?php
require_once(__DIR__ . '/AbstractResult.php');

class CommonResult extends AbstractResult
{
    public
        $Dic,
        $IcDPH,
        $Activity,
        $Created,
        $Cancelled,
        $Warning,
        $WarningUrl
    ;
}