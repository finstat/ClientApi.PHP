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

class ExtendedResult
{
    public
        $Ico,
        $RegisterNumberText,
        $Dic,
        $IcDPH,
        $Name,
        $Street,
        $StreetNumber,
        $ZipCode,
        $City,
        $Activity,
        $IcDphAdditional,
        $Created,
        $District,
        $Region,
        $Cancelled,
        $Url,
        $Warning,
        $WarningUrl,
        $PaymentOrderWarning,
        $PaymentOrderUrl,
        $OrChange,
        $OrChangeUrl,
        $SkNaceCode,
        $SkNaceText,
        $SkNaceDivision,
        $SkNaceGroup,
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
        $WarningLiquidation
        $SelfEmployed,
        $Offices,
        $Subjects
        ;
    }
