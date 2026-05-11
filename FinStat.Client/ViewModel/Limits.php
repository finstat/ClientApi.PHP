<?php

namespace FinStat\Client\ViewModel;

/**
 * Typed view of the `Finstat-{Daily,Monthly}-Limit-{Current,Max}` response
 * headers. Populated by `AbstractFinstatApi::parseResponseRaw` and exposed
 * via `AbstractFinstatApi::GetAPILimitsTyped()`. The legacy
 * `GetAPILimits()` array form is preserved for backwards compatibility.
 *
 * Mirrors `FinstatApi.ViewModel.Limits` in the C# client.
 *
 * @package FinStat\Client\ViewModel
 */
class Limits
{
    /** @var Limit|null Daily quota usage. */
    public $Daily;

    /** @var Limit|null Monthly quota usage. */
    public $Monthly;

    public function __toString(): string
    {
        return sprintf("Daily: %s\nMonthly: %s\n",
            $this->Daily ?? '-',
            $this->Monthly ?? '-');
    }
}
