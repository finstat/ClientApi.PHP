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

class DetailResult extends AddressResult
{
    public
        $Ico,
        $IcDPH,
        $Name,
        $LegalForm,
        $OwnershipType,
        $EmployeeCount,
        $Activity,
        $Created,
        $Cancelled,
        $Url,
        $Warning,
        $WarningUrl,
        $CzNaceCode,
        $CzNaceText,
        $CzNaceDicision,
        $CzNaceGroup
    ;
}
?>