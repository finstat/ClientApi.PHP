<?php

require_once(__DIR__ . '/../../FinStat.Client/ViewModel/AddressResult.php');
require_once(__DIR__ . '/../../FinStat.Client/ViewModel/Detail/CommonResult.php');

class JudgementIndicatorResult
{
    public $Name;
    public $Value;
}

class BankAccount
{
    public $AccountNumber;
    public $PublishedAt;
}

class BaseResult extends CommonResult
{
    public $RegisterNumberText;
    public $SuspendedAsPerson;
    public $OrChange;
    public $OrChangeUrl;
    public $PaymentOrderWarning;
    public $PaymentOrderUrl;
    public $SkNaceCode;
    public $SkNaceText;
    public $SkNaceDivision;
    public $SkNaceGroup;
    public $LegalFormCode;
    public $LegalFormText;
    public $RpvsInsert;
    public $RpvsUrl;
    public $ProfitActual;
    public $RevenueActual;
    public $SalesCategory;
    public $IcDphAdditional;
    public $JudgementIndicators  = array();
    public $JudgementFinstatLink;
    public $HasKaR;
    public $HasDebt;
    public $KaRUrl;
    public $DebtUrl;
    public $Anonymized;
    public $BankAccounts;
    public $TaxReliabilityIndex;
    public $SuspendedAsPersonUntil;
}
