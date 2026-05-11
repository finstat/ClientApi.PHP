<?php

namespace FinStat\ViewModel\Distraint;

/**
 * Bailiff (exekútor) record returned in distraint detail results.
 *
 * Mirrors `FinstatApi.Bailiff` in the C# client.
 *
 * @package FinStat\ViewModel\Distraint
 */
class Bailiff
{
    /** @var int|null */
    public $Id;

    /** @var string|null */
    public $Name;

    /** @var string|null */
    public $Street;

    /** @var string|null */
    public $ZIP;

    /** @var string|null */
    public $City;
}
