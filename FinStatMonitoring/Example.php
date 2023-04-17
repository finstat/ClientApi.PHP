<?php
require_once(__DIR__ . '/../FinStatApi/FinstatMonitoringApi.php');
require_once(__DIR__ . '/../FinStat.Client/ViewModel/Monitoring/MonitoringReportResult.php');

function echoDate($date, $json = false)
{
    if($date && !empty($date)) {
        if($json) {
            $date = new DateTime($date);
        }
        return $date->format('d.m.Y H:i:s');
    }
    return '';
}

function echoException($e)
{
    echo "<h1 style=\"color: red\">Exception</h1>";
    echo"<table>";
    echo"<tr><th>Code:</th><td>{$e->getCode()}</td></tr>";
    echo"<tr><th>Message:</th><td> {$e->getMessage()}</td></tr>";
    echo"<tr><th>Body:</th><td>{$e->getData()}</td></tr>";
    echo"</table>";
    die();
}

function echoMonitoringReport($response, $json)
{
    echo "<pre>";
    echo '<b>Report: </b>'.           '<br />';
    if (!empty($response)) {
        $icodate = "";
        if($report[0] instanceof MonitoringDateReportResult) {
            $icodate  = "Dátum";
        } else {
            $icodate  = "Ičo";
        }
        echo "<table>";
        echo '<tr>'.
            '<th>Identifikátor</th>'.
            '<th>' . $icodate . '</th>'.
            '<th>Názov</th>'.
            '<th>Dátum zverejnenia</th>'.
            '<th>Typ</th>'.
            '<th>Popis</th>'.
            '<th>Url</th>'.
            '</tr>';
        foreach($response as $report) {
            $icodate = "";
            if($report instanceof MonitoringDateReportResult) {
                $icodate  = $report->Date;
            } else {
                $icodate  = $report->Ico;
            }
            echo '<tr>'.
            '<td>'. $report->Ident .'</td>'.
            '<td>'. $icodate .'</td>'.
            '<td>'. $report->Name .'</td>'.
            '<td>'. (($report->PublishDate) ? echoDate($report->PublishDate, $json) : '') .'</td>'.
            '<td>'. $report->Type .'</td>'.
            '<td>'. $report->Description .'</td>'.
            '<td>'. $report->Url .'</td>'.
            '</tr>';
        }
        echo "</table>";
    }
    echo "</pre>";
}

function echoMonitoringList($response)
{
    echo "<pre>";
    echo '<b>List: </b>'.           '<br />';
    if (!empty($response)) {
        echo "<ul>";
        foreach($response as $ico) {
            echo '<li>'. $ico .'</li>';
        }
        echo "</ul>";
    }
    echo "</pre>";
}

function echoLimits($limits)
{
    if(!empty($limits)) {
        echo '<h2>Limity</h2>';
        echo '<table>';
        echo '<tr>'.
                '<th></th>'.
                '<th>Aktuálny</th>'.
                '<th>MAX</th>'.
             '</tr>';
        echo '<tr>'.
               '<th>Denný</th>'.
               '<th>'. ((isset($limits['daily']) && isset($limits['daily']['current'])) ? $limits['daily']['current'] : "---") .'</th>'.
               '<th>'. ((isset($limits['daily']) && isset($limits['daily']['max'])) ? $limits['daily']['max'] : "---") .'</th>'.
            '</tr>';
        echo '<tr>'.
               '<th>Mesačný</th>'.
               '<th>'. ((isset($limits['monthly']) && isset($limits['monthly']['current'])) ? $limits['monthly']['current'] : "---") .'</th>'.
               '<th>'. ((isset($limits['monthly']) && isset($limits['monthly']['max'])) ? $limits['monthly']['max'] : "---") .'</th>'.
            '</tr>';
        echo '</table>';
    }
}

// zakladne prihlasovacie udaje a nastavenia klienta
$apiUrl = 'https://www.finstat.sk/api/';    // URL adresa Finstat API
$apiKey = 'PLEASE_FILL_IN_YOUR_API_KEY';// PLEASE_FILL_IN_YOUR_API_KEY je NEFUNKCNY API kluc. Pre plnu funkcnost API,
// prosim poziadajte o svoj jedinecny kluc na info@finstat.sk.
$privateKey = 'PLEASE_FILL_IN_YOUR_PRIVATE_KEY';// PLEASE_FILL_IN_YOUR_PRIVATE_KEY je NEFUNKCNY API kluc. Pre plnu funkcnost API,
// prosim poziadajte o svoj privatny kluc na info@finstat.sk.
$stationId = 'Api test';                // Identifikátor stanice, ktorá dopyt vygenerovala.
// Môže byť ľubovolný reťazec.
$stationName = 'Api test';                // Názov alebo opis stanice, ktorá dopyt vygenerovala.
// Môže byť ľubovolný reťazec.
$timeout = 10;                            // Dĺžka čakania na odozvu zo servera v sekundách.
$json =  false;                         // Flag ci ma API vraciat odpoved ako JSON

// inicializacia klienta
$api = new FinstatMonitoringApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

// priklad dopytu na detail firmy, ktora ma ICO 35757442
$ico = (isset($_GET['ico']) && !empty($_GET['ico'])) ? $_GET['ico'] : '35757442';
$date = (isset($_GET['date']) && !empty($_GET['date'])) ? $_GET['date'] : "1.1.1991";
?>
<h1>Add test:</h1>
<?php
try {
    // funkcia $api->AddToMonitoring(string) vracia stav úspechu operácie
    if (!empty($ico)) {
        $response = $api->AddToMonitoring($ico, $json);
        $response2 = $api->AddToMonitoring($ico + 'blaaa', $json);
    }
    if (!empty($date)) {
        $response3 = $api->AddDateToMonitoring($date, $json);
    }
} catch (Exception $e) {
    echoException($e);
}

// priklad vypisu ziskanych udajov z Finstatu
header('Content-Type: text/html; charset=utf-8');
echo "<pre>";
echo '<b>OK: </b>'.                 ($response ? "true" : "false") .'<br />';
echo '<b>Fail: </b>'.               ($response2 ? "true" : "false") .'<br />';
echo '<b>OK Date: </b>'.            ($response3 ? "true" : "false") .'<br />';
echo "</pre>";
echo '<hr />';
?>
<h1>MonitoringList test:</h1>
<?php
try {
    // funkcia $api->MonitoringList() vracia zoznam monitorovanych ICO
    if (!empty($ico)) {
        $response = $api->MonitoringList($json);
    }
    if (!empty($date)) {
        $response2 = $api->MonitoringDateList($json);

    }
} catch (Exception $e) {
    echoException($e);
}

// priklad vypisu ziskanych udajov z Finstatu
echoMonitoringList($response);
echoMonitoringList($response2);
echo '<hr />';
?>

<h1>Remove test:</h1>
<?php
try {
    // funkcia $api->RemoveFromMonitoring(string) vracia stav úspechu operácie
    if (!empty($ico)) {
        $response = $api->RemoveFromMonitoring($ico, $json);
    }
    if (!empty($date)) {
        $response2 = $api->RemoveDateFromMonitoring($date, $json);
    }
} catch (Exception $e) {
    echoException($e);
}

// priklad vypisu ziskanych udajov z Finstatu
echo "<pre>";
echo '<b>OK: </b>'.                 ($response ? "true" : "false") .'<br />';
echo '<b>OK Date: </b>'.            ($response2 ? "true" : "false") .'<br />';
echo "</pre>";
echo '<hr />';
?>

<h1>MonitoringReport test:</h1>
<?php
try {
    // funkcia $api->MonitoringReport() vracia zoznam MonitoringReportResult objektov
    if (!empty($ico)) {
        $response = $api->MonitoringReport($json);
    }
    if (!empty($date)) {
        $response2 = $api->MonitoringDateReport($json);
    }
} catch (Exception $e) {
    echoLimits($api->GetAPILimits());
    echoException($e);
}

echoMonitoringReport($response, $json);
echoMonitoringReport($response2, $json);
echoLimits($api->GetAPILimits());
echo '<hr />';
?>