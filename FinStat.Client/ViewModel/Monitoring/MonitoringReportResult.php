<?php

class AbstractMonitoringReportResult
{
    public $Ident;
    public $Name;
    public $PublishDate;
    public $Type;
    public $Description;
    public $Url;
    public $Categories;
}

class MonitoringReportResult extends AbstractMonitoringReportResult
{
    public $Ico;
}

class MonitoringDateReportResult extends AbstractMonitoringReportResult
{
    public $Date;
}

class MonitoringCategory
{
    public $Category;
    public $Name;
}
