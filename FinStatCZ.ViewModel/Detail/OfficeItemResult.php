<?php

namespace FinStatCZ\ViewModel\Detail;

use FinStat\ViewModel\Detail\OfficeResult;

class OfficeItemResult extends OfficeResult
{
    const TYPE_ESTABLISHMENT = 'Establishment';

    // left out when not available (RŽP office name)
    public $Name;
}
