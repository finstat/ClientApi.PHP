<?php

namespace FinStatCZ\ViewModel\Detail;

class RZPPersonResult
{
    const FUNCTION_ENTREPRENEUR = 'Entrepreneur';
    const FUNCTION_RESPONSIBLE_PERSON = 'ResponsiblePerson';

    public $FullName;
    public $BirthDate;
    public $Function;
    public $FunctionFrom;
    // trade licences the responsible person answers for, empty for Entrepreneur
    public $Subjects = array();
}
