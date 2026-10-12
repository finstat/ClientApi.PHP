<?php

namespace FinStatCZ\ViewModel\Detail;

class IsirProceedingResult
{
    public $FileReference;
    public $Url;
    public $Court;
    public $FirstEvent;
    public $LastEvent;
    public $Status;
    /** @var IsirPartyResult[] */
    public $Debtors = array();

    // UltimateCZ only
    public $Type;
    public $StartDate;
    /** @var IsirPartyResult[] */
    public $Officers = array();
    public $CreditorsCount;
    /** @var IsirPartyResult[] */
    public $Creditors = array();
    public $AmountOfClaims;
    /** @var ReceivableApplicationResult[] */
    public $ReceivableApplications = array();
}
