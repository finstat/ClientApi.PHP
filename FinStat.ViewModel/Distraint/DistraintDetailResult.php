<?php

namespace FinStat\ViewModel\Distraint;

/**
 * Full distraint detail — extends the preview with court and enforcement
 * data. Mirrors `FinstatApi.DistraintDetailResult` in the C# client.
 *
 * @package FinStat\ViewModel\Distraint
 */
class DistraintDetailResult extends DistraintPreview
{
    /** @var string|null Súd. */
    public $Court;

    /** @var \DateTime|null Dátum poverenia. */
    public $DateOfAuthorisation;

    /** @var string|null Súdna značka. */
    public $CourtCode;

    /** @var string|null Popis nároku. */
    public $EnforcementDetails;

    /** @var float|null Výška plnenia. */
    public $SumOutstanding;

    /** @var string|null Mena. */
    public $Currency;

    /** @var Bailiff|null Exekútor. */
    public $Bailiff;
}
