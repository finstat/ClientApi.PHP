<?php

namespace FinStatCZ\ViewModel\Detail;

use FinStat\ViewModel\Detail\PersonResult;

class ORCRPersonResult extends PersonResult
{
    /** @var PersonFunctionResult[] */
    public $Functions;
    // filled when the person is a company
    public $Ico;
}
