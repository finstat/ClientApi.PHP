<?php
require_once('../FinStat.Client/ViewModel/AddressResult.php');
require_once('../FinStat.Client/ViewModel/Detail/AbstractBaseResult.php');

class JudgementIndicatorResult
{
    public
    $Name,
    $Value
    ;
}

class BaseResult extends AbstractBaseResult
{
    public
        $RegisterNumberText,
        $Dic,
        $SuspendedAsPerson,
        $OrChange,
        $OrChangeUrl,
        $PaymentOrderWarning,
        $PaymentOrderUrl,
        $SkNaceCode,
        $SkNaceText,
        $SkNaceDivision,
        $SkNaceGroup,
        $LegalFormCode,
        $LegalFormText,
        $RpvsInsert,
        $RpvsUrl,
        $ProfitActual,
        $RevenueActual,
        $SalesCategory,
        $IcDphAdditional,
        $JudgementIndicators  = array(),
        $JudgementFinstatLink
    ;
}
?>