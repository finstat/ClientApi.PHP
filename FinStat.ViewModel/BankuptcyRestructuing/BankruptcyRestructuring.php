<?php

require_once(__DIR__ . '/../../FinStat.Client/ViewModel/AddressResult.php');

class BankruptcyRestructuring
{
    public $Debtors;
    public $FileReference;
    public $FirstRecordDate; 
    public $LastRecordDate;
    public $RUState;
    public $RUStateDate;
    public $OVState;
    public $OVStateDate;
    public $EnterDate;
    public $ExitDate;
    public $EndState;
    public $EndReason;
    public $Deadlines;
    public $FinstatURL;
}