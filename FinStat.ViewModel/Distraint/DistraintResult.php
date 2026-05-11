<?php

namespace FinStat\ViewModel\Distraint;

/**
 * Distraint search result — count + page of previews.
 *
 * Mirrors `FinstatApi.DistraintResult` in the C# client.
 *
 * @package FinStat\ViewModel\Distraint
 */
class DistraintResult
{
    /** @var int|null */
    public $Count;

    /** @var DistraintPreview[] */
    public $Distraints = array();
}
