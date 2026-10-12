<?php

namespace FinStatCZ\ViewModel\Detail;

/**
 * Claims filed by the company as creditor in active insolvency proceedings (at most 300 latest proceedings).
 */
class FiledInsolvencyClaimsResult
{
    public $ProceedingsCount;
    // null when no application was scanned, so unknown is not reported as 0
    public $AmountOfClaims;
    /** @var FiledClaimApplicationResult[] */
    public $Applications = array();
}
