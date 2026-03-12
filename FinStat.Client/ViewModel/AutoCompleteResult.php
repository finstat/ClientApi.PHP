<?php

namespace FinStat\Client\ViewModel;

class CompanyResult
{
    public $Ico;
    public $Name;
    public $City;
    public $Cancelled;
}

class AutoCompleteResult
{
    public $Results = array();
    public $Suggestions = array();
}
