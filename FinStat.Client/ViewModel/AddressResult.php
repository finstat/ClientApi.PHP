<?php

namespace FinStat\Client\ViewModel;

/**
 * Plain address payload — Street, ZipCode, City, District, Region, Country.
 *
 * Companion types `BaseInfo` and `PersonAddressResult` used to live in this
 * file; both have been moved to their own files (BaseInfo.php and
 * PersonAddressResult.php) so PSR-4 autoload can resolve each class without
 * relying on this file having been loaded first.
 *
 * @package FinStat\Client\ViewModel
 */
class AddressResult
{
    /** @var string|null */
    public $Name;

    /** @var string|null */
    public $Street;

    /** @var string|null */
    public $StreetNumber;

    /** @var string|null */
    public $ZipCode;

    /** @var string|null */
    public $City;

    /** @var string|null */
    public $District;

    /** @var string|null */
    public $Region;

    /** @var string|null */
    public $Country;
}
