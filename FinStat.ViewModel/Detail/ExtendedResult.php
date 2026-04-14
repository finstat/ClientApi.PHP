<?php

namespace FinStat\ViewModel\Detail;

class ExtendedResult extends BaseResult
{
    public $Phones = array();
    public $Emails = array();
    public $EmployeeCode;
    public $EmployeeText;
    public $OwnershipTypeCode;
    public $OwnershipTypeText;
    public $ActualYear;
    public $CreditScoreValue;
    public $CreditScoreState;
    public $BasicCapital;
    public $ProfitPrev;
    public $RevenuePrev;
    public $ForeignResources;
    public $GrossMargin;
    public $ROA;
    public $Debts = array();
    public $PaymentOrders = array();
    public $WarningKaR;
    public $WarningLiquidation;
    public $SelfEmployed;
    public $Offices;
    public $Subjects;
    public $StructuredName;
    public $ContactSources;
    public $DisposalUrl;
    public $HasDisposal;
    public $JudgementCounts = array();
    public $JudgementLastPublishedDate;
    public $Ratios  = array();
    public $StateReceivables = array();
    public $CommercialReceivables = array();
    public $CreditScoreValueIndex05;
    public $CreditScoreStateIndex05;
    public $DistraintsAuthorization;
    public $CreditScoreValueFinStatScore;
    public $CreditScoreStateFinStatScore;
}
