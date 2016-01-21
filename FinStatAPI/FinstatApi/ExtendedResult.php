<?php

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

class OfficeResult
{
    public
        $City,
        $Country,
        $District,
        $Region,
        $Street,
        $StreetNumber,
        $Subjects,
        $ZipCode,
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

class ExtendedResult extends BaseResult
{
    public
        $IcDphAdditional,
        $District,
        $Region,
        $Phones = array(),
        $Emails = array(),
        $EmployeeCode,
        $EmployeeText,
        $LegalFormCode,
        $LegalFormText,
        $OwnershipTypeCode,
        $OwnershipTypeText,
        $ActualYear,
        $CreditScoreValue,
        $CreditScoreState,
        $ProfitActual,
        $ProfitPrev,
        $RevenueActual,
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
        $StructuredName
        ;
    }
