<?php

namespace FinStat\ViewModel\Distraint;

/**
 * Distraint preview row — what the distraint search returns before the
 * caller drills into a specific authorisation for full detail.
 *
 * Mirrors `FinstatApi.DistraintPreview` in the C# client.
 *
 * @package FinStat\ViewModel\Distraint
 */
class DistraintPreview
{
    /** @var string|null Značka. */
    public $Code;

    /** @var Debtor[] Povinní. */
    public $Debtors = array();

    /** @var int|null TypPoverenia. */
    public $TypeOfAuthorisation;

    /** @var \DateTime|null */
    public $Created;

    /** @var int|null */
    public $DetailId;

    /** @var string|null */
    public $DetailToken;

    /** @var string|null */
    public $StoredDetailId;

    /** @var Debtor[] Oprávnení. */
    public $Pledgers = array();
}
