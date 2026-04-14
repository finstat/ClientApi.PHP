<?php

namespace FinStat\ViewModel\Detail;

use FinStat\Client\ViewModel\AddressResult;

class AbstractPersonResult extends AddressResult
{
    public $FullName;
    public $StructuredName;
    public $DetectedFrom;
    public $DetectedTo;
    public $Functions = array();
}
