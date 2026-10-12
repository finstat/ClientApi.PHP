<?php

namespace FinStatCZ\ViewModel\Detail;

class PremiumCZResult extends DetailResult
{
    public $VatNumber;
    public $TaxPayer;
    public $BankAccounts = array();
    public $SuspendedAsPerson;
    public $SuspendedAsPersonUntil;
    public $LegalFormCode;
    public $OwnershipCode;
    public $UnReliability;
    public $RegisterNumberText;
    public $TradeLicensingOffice;
    public $ActualYear;
    public $SalesActual;
    public $ProfitActual;
    public $Sales;
    public $Profit;
    /** @var TurnoverCategoryResult|null */
    public $TurnoverCategory;
}
