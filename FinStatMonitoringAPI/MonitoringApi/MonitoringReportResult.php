<?php

class AbstractMonitoringReportResult
{
    public
        $Ident,
        $Name,
        $PublishDate,
        $Type,
        $Description,
        $Url;
}

class MonitoringReportResult extends AbstractMonitoringReportResult
{
    public
        $Ico
    ;
}

class MonitoringDateReportResult extends AbstractMonitoringReportResult
{
    public
        $Date
    ;
}
?>