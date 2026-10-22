<?php

namespace FinStatCZ\ViewModel\Detail;

use FinStat\Client\ViewModel\Detail\AbstractResult;

class BasicResult extends AbstractResult
{
    public $VatNumber;
    // VatPayer, IdentifiedPerson or PersonIdentifiedForTax; empty when not registered for VAT
    public $TaxPayer;
}
