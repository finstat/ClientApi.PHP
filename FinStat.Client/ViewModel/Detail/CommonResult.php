<?php

require_once(__DIR__ . '/AbstractResult.php');

class CommonResult extends AbstractResult
{
    public $Activity;
    public $Created;
    public $Cancelled;
    public $Warning;
    public $WarningUrl;
}
