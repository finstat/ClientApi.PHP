<?php

namespace FinStatCZ\ViewModel\Detail;

class UltimateCZResult extends EliteCZResult
{
    /** @var ORCRPersonResult[] */
    public $ORCRPersons = array();
    /** @var RZPPersonResult[] */
    public $RZPPersons = array();
    public $StatutoryAction;
    public $ProcurationAction;
    // active trade licences for RŽP subjects, active ORČR business subjects otherwise
    /** @var \FinStat\ViewModel\Detail\SubjectResult[] */
    public $Subjects = array();
    // 4 years of history as FinancialIndicators
    /** @var CreditScoreResult[] */
    public $CreditScores = array();
    /** @var FiledInsolvencyClaimsResult|null */
    public $FiledInsolvencyClaims;
}
