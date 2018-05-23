<?php
require_once(__DIR__ . '/BaseResult.php');

class DebtResult
{
    public
        $Source,
        $Value,
        $ValidFrom
    ;
}

class PaymentOrderResult
{
    public
        $PublishDate,
        $Value
    ;
}

class IcDphAdditionalResult
{
    public
        $IcDph,
        $Paragraph,
        $CancelListDetectedDate,
        $RemoveListDetectedDate
    ;
}

class OfficeResult extends AddressResult
{
    public
        $Subjects,
        $Type
        ;
}

class SubjectResult
{
    public
        $Title,
        $ValidFrom,
        $SuspendedFrom
    ;
}

class NamePartsResult
{
    public
        $Prefix,
        $Name,
        $Suffix,
        $After
    ;
}

class ContactSourceResult
{
    public
        $Contact,
        $Sources
    ;
}
class JudgementCountResult
{
    public
        $Name,
        $Value
    ;
}

class RatioResult
{
    public
    $Name,
    $Values
    ;
}

class RatioItemResult
{
    public
    $Year,
    $Value;
}

class ExtendedResult extends BaseResult
{
    public
        $Phones = array(),
        $Emails = array(),
        $EmployeeCode,
        $EmployeeText,
        $OwnershipTypeCode,
        $OwnershipTypeText,
        $ActualYear,
        $CreditScoreValue,
        $CreditScoreState,
        $BasicCapital,
        $ProfitPrev,
        $RevenuePrev,
        $ForeignResources,
        $GrossMargin,
        $ROA,
        $Debts = array(),
        $PaymentOrders = array(),
        $WarningKaR,
        $WarningLiquidation,
        $SelfEmployed,
        $Offices,
        $Subjects,
        $StructuredName,
        $ContactSources,
        $KaRUrl,
        $DebtUrl,
        $DisposalUrl,
        $HasKaR,
        $HasDebt,
        $HasDisposal,
        $JudgementCounts = array(),
        $JudgementLastPublishedDate,
        $Ratios  = array()
        ;
    }
