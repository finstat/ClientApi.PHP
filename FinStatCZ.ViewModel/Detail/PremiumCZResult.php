<?php

namespace FinStatCZ\ViewModel\Detail;

class PremiumCZResult extends DetailResult
{
    public $VatNumber;
    // VatPayer, IdentifiedPerson or PersonIdentifiedForTax; empty when not registered for VAT
    public $TaxPayer;
    public $BankAccounts = array();
    public $SuspendedAsPerson;
    public $SuspendedAsPersonUntil;
    public $LegalFormCode;
    public $OwnershipCode;
    // employee count category code from the latest year that has one
    public $EmployeeCode;
    public $UnReliability;
    public $RegisterNumberText;
    public $TradeLicensingOffice;
    public $ActualYear;
    public $SalesActual;
    public $ProfitActual;
    // EliteCZ/UltimateCZ leave Sales and Profit out without financial data, they stay empty
    public $Sales;
    public $Profit;
    /** @var TurnoverCategoryResult|null */
    public $TurnoverCategory;
}
