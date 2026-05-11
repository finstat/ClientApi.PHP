<?php

namespace FinStat\Client\ViewModel;

/**
 * Address with the related-person `Ico` and `BirthDate` attached.
 *
 * Previously co-located with `AddressResult` / `BaseInfo` in
 * `AddressResult.php`. Split out so PSR-4 autoload can resolve
 * `PersonAddressResult` directly.
 *
 * @package FinStat\Client\ViewModel
 */
class PersonAddressResult extends AddressResult
{
    /** @var string|null */
    public $Ico;

    /** @var \DateTime|string|null */
    public $BirthDate;
}
