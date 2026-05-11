<?php

namespace FinStat\ViewModel\Distraint;

/**
 * Debtor (povinný) or pledger (oprávnený) of a distraint authorisation.
 *
 * Mirrors `FinstatApi.Debtor` in the C# client.
 *
 * @package FinStat\ViewModel\Distraint
 */
class Debtor
{
    /** @var string|null */
    public $Type;

    /** @var string|null */
    public $Name;

    /** @var string|null */
    public $Surname;

    /** @var \DateTime|null */
    public $DateOfBirth;

    /** @var string|null Rodné číslo. */
    public $IdentificationNumber;

    /** @var string|null */
    public $ICO;

    /** @var string|null */
    public $CompanyName;

    /** @var string|null */
    public $AddressType;

    /** @var string|null */
    public $Street;

    /** @var string|null */
    public $City;

    /** @var string|null */
    public $ZIP;
}
