<?php

namespace FinStat\Client\ViewModel;

/**
 * Current / max usage pair for one quota window (daily or monthly).
 *
 * Mirrors `FinstatApi.ViewModel.Limit` in the C# client.
 *
 * @package FinStat\Client\ViewModel
 */
class Limit
{
    /** @var int|null Current usage. */
    public $Current;

    /** @var int|null Maximum allowed in the window. */
    public $Max;

    public function __toString(): string
    {
        return sprintf('%s/%s', $this->Current ?? '?', $this->Max ?? '?');
    }
}
