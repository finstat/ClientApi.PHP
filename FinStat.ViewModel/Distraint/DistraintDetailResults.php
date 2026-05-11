<?php

namespace FinStat\ViewModel\Distraint;

/**
 * Wrapper for a batch of distraint detail results returned by the
 * distraint-details endpoint.
 *
 * Mirrors `FinstatApi.DistraintDetailResults` in the C# client.
 *
 * @package FinStat\ViewModel\Distraint
 */
class DistraintDetailResults
{
    /** @var DistraintDetailResult[] */
    public $DistraintDetails = array();
}
