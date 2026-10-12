<?php

namespace FinStatCZ\ViewModel\Detail;

class VehicleFleetResult
{
    public $Year;
    public $ActiveCount;
    public $Url;

    // UltimateCZ only, breakdowns of VehicleCount for Year
    public $VehicleCount;
    /** @var VehicleFleetItemResult[] */
    public $OwnershipTypes = array();
    /** @var VehicleFleetItemResult[] */
    public $VehicleTypes = array();
    /** @var VehicleFleetItemResult[] */
    public $ProductionYears = array();
    /** @var VehicleFleetBrandResult[] */
    public $TopBrands = array();
}
