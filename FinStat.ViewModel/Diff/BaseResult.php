<?php

namespace FinStat\ViewModel\Diff;

/**
 * Per-row baseline payload from a daily diff feed (Basic tier).
 *
 * Mirrors `FinstatApi.ViewModel.Diff.BaseResult` in the C# client. Note that
 * Diff payloads are intentionally separate from `FinStat\ViewModel\Detail\*`
 * — the field set differs from the live Detail endpoints (e.g. fewer credit
 * fields and no JudgementIndicators).
 *
 * @package FinStat\ViewModel\Diff
 */
class BaseResult
{
    /** @var string|null */
    public $Ico;

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
    public $Activity;

    /** @var \DateTime|null */
    public $Created;

    /** @var \DateTime|null */
    public $Cancelled;

    /** @var string|null */
    public $Url;

    /** @var bool|null */
    public $Warning;

    /** @var string|null */
    public $WarningUrl;

    /** @var bool|null */
    public $OrChange;

    /** @var string|null */
    public $OrChangeUrl;

    /** @var string|null */
    public $RegisterNumberText;

    /** @var string|null */
    public $Dic;

    /** @var string|null */
    public $IcDPH;

    /** @var bool|null */
    public $SuspendedAsPerson;

    /** @var bool|null */
    public $PaymentOrderWarning;

    /** @var string|null */
    public $PaymentOrderUrl;
}
