<?php

namespace FinStatCZ\ViewModel\Detail;

use FinStat\ViewModel\Detail\FunctionResult;

/**
 * Person function with a language-neutral Type code (Statutory, Partner, Shareholder, Procuration,
 * BeneficialOwner..., ResponsiblePerson, Entrepreneur, Other ...); Description keeps the name from the register.
 */
class PersonFunctionResult extends FunctionResult
{
    // translated label of Type in the response language
    public $TypeText;
}
