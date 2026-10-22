<?php

namespace FinStatCZ\ViewModel\Detail;

class IsirProceedingResult
{
    // ISIR proceeding status (druhStavRizeni) as a language-neutral code; a code unknown to the API is Unknown
    const STATUS_UNKNOWN = 'Unknown';
    const STATUS_PENDING_INSOLVENCY_DECISION = 'PendingInsolvencyDecision';
    const STATUS_MORATORIUM = 'Moratorium';
    const STATUS_INSOLVENCY = 'Insolvency';
    const STATUS_BANKRUPTCY = 'Bankruptcy';
    const STATUS_DEBT_RELIEF = 'DebtRelief';
    const STATUS_REORGANIZATION = 'Reorganization';
    const STATUS_RESOLVED = 'Resolved';
    const STATUS_ANNULLED_BY_HIGH_COURT = 'AnnulledByHighCourt';
    const STATUS_BANKRUPTCY_AFTER_ANNULMENT = 'BankruptcyAfterAnnulment';
    const STATUS_REOPENED = 'Reopened';
    const STATUS_TRANSFERRED = 'Transferred';
    const STATUS_FINAL = 'Final';
    const STATUS_CHECKED = 'Checked';
    const STATUS_ERRONEOUS_RECORD = 'ErroneousRecord';

    public $FileReference;
    public $Url;
    // ISIR court code (e.g. KSJIMBM), language neutral
    public $Court;
    public $CourtName;
    public $FirstEvent;
    public $LastEvent;
    // one of the STATUS_* codes
    public $Status;
    // translated label of Status; the raw ISIR code when the code is new (Status = Unknown)
    public $StatusText;
    /** @var IsirPartyResult[] */
    public $Debtors = array();

    // UltimateCZ only; STATUS_BANKRUPTCY, STATUS_DEBT_RELIEF or STATUS_REORGANIZATION once declared
    public $Type;
    public $TypeText;
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
