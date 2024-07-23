<?php

namespace FinstatApiCz;

require_once(__DIR__ . '/../../FinStat.Client/ViewModel/AddressResult.php');
require_once(__DIR__ . '/../../FinStat.Client/ViewModel/Detail/AbstractResult.php');
require_once(__DIR__ . '/DetailResult.php');

class PremiumCZResult extends DetailResult
{
    public $VatNumber;
    public $TaxPayer;
    public $BankAccounts = array();
    public $SuspendedAsPerson;
    public $LegalFormCode;
    public $OwnershipCode;
    public $UnReliability;
    public $RegisterNumberText;
    public $ActualYear;
    public $SalesActual;
    public $ProfitActual;
    public $Sales;
    public $Profit;
}
