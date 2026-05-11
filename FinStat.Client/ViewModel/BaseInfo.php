<?php

namespace FinStat\Client\ViewModel;

/**
 * Lightweight `{Name, Ico}` pair returned by endpoints that reference a
 * company without committing to the full address/detail payload —
 * currently used by `DistraintsAuthorizationDetail.Authorized` on the
 * Ultimate Detail result.
 *
 * Previously co-located with `AddressResult` / `PersonAddressResult` in
 * `AddressResult.php`. Split out so PSR-4 autoload can resolve `BaseInfo`
 * directly without relying on `AddressResult` being touched first.
 *
 * Mirrors `FinstatApi.BaseInfo` in the C# client.
 *
 * @package FinStat\Client\ViewModel
 */
class BaseInfo
{
    /** @var string|null */
    public $Name;

    /** @var string|null */
    public $Ico;
}
