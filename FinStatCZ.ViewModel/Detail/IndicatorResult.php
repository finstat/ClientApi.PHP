<?php

namespace FinStatCZ\ViewModel\Detail;

class IndicatorResult
{
    // same names as the SK Ratios: Profit, Sales, Assets, Equity, FinancialLeverage, ShareCapital, EBIT, EBITDA,
    // ValueAdded, ROA, ROE, LiabilitiesToAssets, Depreciation, NetTurnover, ROAEbit
    public $Name;
    /** @var IndicatorValueResult[] */
    public $Values = array();
}
