<?php

require_once(__DIR__ . '/../../FinStat.Client/ViewModel/AddressResult.php');
require_once(__DIR__ . '/../../FinStat.Client/ViewModel/Detail/AbstractResult.php');

class BasicResult extends AbstractResult
{
    public $Dic;
    public $IcDPH;
    public $Anonymized;
}
