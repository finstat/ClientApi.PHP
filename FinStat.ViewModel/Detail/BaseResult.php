<?php
require_once(__DIR__ . '/../../FinStat.Client/ViewModel/AddressResult.php');
require_once(__DIR__ . '/../../FinStat.Client/ViewModel/Detail/CommonResult.php');

class JudgementIndicatorResult
{
    public
        $Name,
        $Value
    ;
}

class BankAccount
{
    public
        $AccountNumber,
        $PublishedAt
    ;
}

class BaseResult extends CommonResult
{
    public
        $RegisterNumberText,
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
        $JudgementFinstatLink,
        $HasKaR,
        $HasDebt,
        $KaRUrl,
        $DebtUrl,
        //$Gdpr,
        $BankAccounts
    ;
}
?>