<?php

class AddressResult
{
    public
        $Name,
        $Street,
        $StreetNumber,
        $ZipCode,
        $City,
        $District,
        $Region,
        $Country
    ;
}

class BaseResult extends AddressResult
{
    public
        $Ico,
        $RegisterNumberText,
        $Dic,
        $IcDPH,
        $Activity,
        $Created,
        $Cancelled,
        $SuspendedAsPerson,
        $Url,
        $Warning,
        $WarningUrl,
        $OrChange,
        $OrChangeUrl,
        $PaymentOrderWarning,
        $PaymentOrderUrl,
        $SkNaceCode,
        $SkNaceText,
        $SkNaceDivision,
        $SkNaceGroup,
        $LegalFormCode,
        $LegalFormText
    ;
}
?>