<?php

namespace FinStat\ViewModel\Diff;

/**
 * Per-row Extended-tier payload from a daily diff feed.
 *
 * Mirrors `FinstatApi.ViewModel.Diff.ExtendedResult` in the C# client.
 * Nested helper shapes (Debt, PaymentOrder, IcDphAdditonalData) are kept
 * untyped (`array` / `\stdClass`) to avoid duplicating value types that the
 * PHP side does not actively parse — consumers iterating over diff rows can
 * project them into typed structs locally.
 *
 * @package FinStat\ViewModel\Diff
 */
class ExtendedResult extends BaseResult
{
    /** @var \stdClass|null IcDph + Paragraph + CancelListDetectedDate + RemoveListDetectedDate. */
    public $IcDphAdditional;

    /** @var string|null */
    public $SkNaceCode;

    /** @var string|null */
    public $SkNaceText;

    /** @var string|null */
    public $SkNaceDivision;

    /** @var string|null */
    public $SkNaceGroup;

    /** @var string|null */
    public $District;

    /** @var string|null */
    public $Region;

    /** @var string[] */
    public $Phones = array();

    /** @var string[] */
    public $Emails = array();

    /** @var \stdClass[] Each row: { Source, Value, ValidFrom }. */
    public $Debts = array();

    /** @var \stdClass[] Each row: { PublishDate, Value }. */
    public $PaymentOrders = array();

    /** @var string|null */
    public $EmployeeCode;

    /** @var string|null */
    public $EmployeeText;

    /** @var string|null */
    public $LegalFormCode;

    /** @var string|null */
    public $LegalFormText;

    /** @var string|null */
    public $OwnershipTypeCode;

    /** @var string|null */
    public $OwnershipTypeText;

    /** @var int|null */
    public $ActualYear;

    /** @var float|null */
    public $CreditScoreValue;

    /** @var string|null */
    public $CreditScoreState;

    /** @var float|null */
    public $ProfitActual;

    /** @var float|null */
    public $ProfitPrev;

    /** @var float|null */
    public $RevenueActual;

    /** @var float|null */
    public $RevenuePrev;

    /** @var float|null */
    public $ForeignResources;

    /** @var float|null */
    public $GrossMargin;

    /** @var float|null */
    public $ROA;

    /** @var \DateTime|null */
    public $WarningKaR;

    /** @var \DateTime|null */
    public $WarningLiquidation;
}
