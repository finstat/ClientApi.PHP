<?php

namespace FinStat\ViewModel\Diff;

/**
 * Per-row statement payload from the daily statement diff feed.
 *
 * Mirrors `FinstatApi.ViewModel.Diff.StatementResult` in the C# client.
 * Each entry in Assets / LiabilitiesAndEquity / Income is a `\stdClass`
 * with the shape { Key, Actual, Previous } (StatementValue).
 *
 * @package FinStat\ViewModel\Diff
 */
class StatementResult
{
    /** @var string|null */
    public $ICO;

    /** @var string|null */
    public $Name;

    /** @var int|null */
    public $Year;

    /** @var \DateTime|null */
    public $DateFrom;

    /** @var \DateTime|null */
    public $DateTo;

    /** @var \DateTime|null */
    public $DatePublished;

    /** @var string|null */
    public $Format;

    /** @var string|null */
    public $OriginalFormat;

    /** @var string|null */
    public $Source;

    /** @var \stdClass[] StatementValue[] — { Key, Actual, Previous }. */
    public $Assets = array();

    /** @var \stdClass[] StatementValue[]. */
    public $LiabilitiesAndEquity = array();

    /** @var \stdClass[] StatementValue[]. */
    public $Income = array();
}
