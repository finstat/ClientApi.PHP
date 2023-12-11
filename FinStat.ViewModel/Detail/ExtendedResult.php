<?php

require_once(__DIR__ . '/BaseResult.php');

class DebtResult
{
    public $Source;
    public $Value;
    public $ValidFrom;
}

class ReceivableDebtResult extends DebtResult
{
}

class PaymentOrderResult
{
    public $PublishDate;
    public $Value;
}

class IcDphAdditionalResult
{
    public $IcDph;
    public $Paragraph;
    public $CancelListDetectedDate;
    public $RemoveListDetectedDate;
}

class OfficeResult extends AddressResult
{
    public $Subjects;
    public $Type;
}

class SubjectResult
{
    public $Title;
    public $ValidFrom;
    public $SuspendedFrom;
    public $SuspendedTo;
}

class NamePartsResult
{
    public $Prefix;
    public $Name;
    public $Suffix;
    public $After;
}

class ContactSourceResult
{
    public $Contact;
    public $Sources;
}
class JudgementCountResult
{
    public $Name;
    public $Value;
}

class RatioResult
{
    public $Name;
    public $Values;
}

class RatioItemResult
{
    public $Year;
    public $Value;
}

class DistraintsAuthorizationInfoResult
{
    public $LastPublishDate;
    public $Count;
}

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
