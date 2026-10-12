<?php

namespace FinStatCZ\ViewModel\Detail;

/**
 * Claim application filed in an ISIR proceeding.
 */
class ReceivableApplicationResult
{
    /** @var IsirPartyResult|null */
    public $Creditor;
    // creditor's representative has no birth date in ISIR, only Name and Ico are filled
    /** @var IsirPartyResult|null */
    public $Representative;
    public $PublishDate;
    // null when the application document was not scanned reliably
    public $ClaimsCount;
    public $TotalAmount;
}
