<?php

namespace FinStatCZ\ViewModel\Detail;

class FiledClaimApplicationResult
{
    public $FileReference;
    public $Url;
    /** @var IsirPartyResult[] */
    public $Debtors = array();
    /** @var IsirPartyResult|null */
    public $Representative;
    public $PublishDate;
    public $ClaimsCount;
    public $TotalAmount;
}
