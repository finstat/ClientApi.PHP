<?php

namespace FinStat\ViewModel\Diff;

/**
 * Per-row Ultimate-tier payload from a daily diff feed.
 *
 * Mirrors `FinstatApi.ViewModel.Diff.UltimateResult` in the C# client. The
 * deeply nested helper shapes (Person, RpvsPerson, Office, Subject, Court,
 * HistoryAddress, FunctionAssigment, JudgementCount, JudgementIndicator,
 * Deadline, BaseLiquidationResult, LiquidationResult, ProceedingResult,
 * BankruptResult, RestructuringResult, ContactSource, ReceivableDebt,
 * NameParts) are kept untyped (`array` of `\stdClass`) to avoid duplicating
 * 20+ value types that the PHP client does not parse — the Diff payload is
 * downloaded as a file, not consumed as a streaming SimpleXML feed.
 *
 * Consumers iterating these rows can project the nested objects locally;
 * the property names match the C# side field-for-field.
 *
 * @package FinStat\ViewModel\Diff
 */
class UltimateResult extends ExtendedResult
{
    /** @var string|null */
    public $Country;

    /** @var \DateTime|null */
    public $ORCancelled;

    /** @var \DateTime|null */
    public $ORRemoved;

    /** @var bool|null */
    public $SelfEmployed;

    /** @var \stdClass|null NameParts: { Prefix, Name, Suffix, After }. */
    public $StructuredName;

    /** @var string|null */
    public $ORSection;

    /** @var string|null */
    public $ORInsertNo;

    /** @var \stdClass|null Court (FullAddress + Name). */
    public $RegistrationCourt;

    /** @var string|null */
    public $ProcurationAction;

    /** @var string|null */
    public $StatutoryAction;

    /** @var \stdClass[] Office[] — Address + Subjects[] + Type. */
    public $Offices = array();

    /** @var \stdClass[] ContactSource[] — { Contact, Sources[] }. */
    public $ContactSources = array();

    /** @var string[] */
    public $WebPages = array();

    /** @var int|null */
    public $EmployeesNumber;

    /** @var \stdClass[] Subject[] — { Title, ValidFrom, SuspendedFrom }. */
    public $Subjects = array();

    /** @var \stdClass[] HistoryAddress[]. */
    public $AddressHistory = array();

    /** @var \stdClass[] Person[]. */
    public $Persons = array();

    /** @var string|null */
    public $RpvsInsert;

    /** @var string|null */
    public $RpvsUrl;

    /** @var \stdClass[] RpvsPerson[]. */
    public $RpvsPersons = array();

    /** @var bool|null */
    public $HasDebt;

    /** @var string|null */
    public $DebtUrl;

    /** @var bool|null */
    public $HasDisposal;

    /** @var string|null */
    public $DisposalUrl;

    /** @var \DateTime|null */
    public $LiqDeadline;

    /** @var string|null */
    public $KarUrl;

    /** @var \stdClass|null BankruptResult. */
    public $Bankrupt;

    /** @var \stdClass|null RestructuringResult. */
    public $Restructuring;

    /** @var \stdClass|null LiquidationResult. */
    public $Liquidation;

    /** @var \stdClass|null ProceedingResult. */
    public $OtherProceeding;

    /** @var \stdClass[] JudgementIndicator[]. */
    public $JudgementIndicators = array();

    /** @var string|null */
    public $JudgementFinstatLink;

    /** @var \stdClass[] JudgementCount[]. */
    public $JudgementCounts = array();

    /** @var \DateTime|null */
    public $JudgementLastPublishedDate;

    /** @var float|null */
    public $BasicCapital;

    /** @var float|null */
    public $PaybackRange;

    /** @var string|null */
    public $SalesCategory;

    /** @var string|null */
    public $EmployeeYear;

    /** @var \DateTime|null */
    public $ORLiquidationDate;

    /** @var \stdClass[] ReceivableDebt[]. */
    public $StateReceivables = array();
}
