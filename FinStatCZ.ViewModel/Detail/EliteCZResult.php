<?php

namespace FinStatCZ\ViewModel\Detail;

class EliteCZResult extends PremiumCZResult
{
    // current VAT registration is a group registration (skupina DPH)
    public $VatGroupMember;
    /** @var ContactResult[] */
    public $Contacts = array();
    /** @var CzNaceResult[] */
    public $OtherCzNaces = array();
    /** @var OfficesResult|null */
    public $Offices;
    /** @var VehicleFleetResult|null */
    public $VehicleFleet;
    /** @var IsirProceedingResult[] */
    public $IsirProceedings = array();
    // 2 years in EliteCZ, 4 years in UltimateCZ
    /** @var IndicatorResult[] */
    public $FinancialIndicators = array();
}
