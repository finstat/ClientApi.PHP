<?php

namespace FinStatCZ\ViewModel\Detail;

/**
 * Claims filed by the company as creditor in active insolvency proceedings; counts and amount cover all of them, Applications at most 300 from the latest proceedings.
 */
class FiledInsolvencyClaimsResult
{
    public $ProceedingsCount;
    // null when no amount is known (no active proceeding or no scanned application), so unknown is not reported as 0
    public $AmountOfClaims;
    /** @var FiledClaimApplicationResult[] */
    public $Applications = array();
}
