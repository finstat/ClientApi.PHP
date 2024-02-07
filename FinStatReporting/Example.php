
<?php
require_once(__DIR__ . '/../FinStatApi/FinstatReportingApi.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Reporting/ReportingResult.php');

function echoDate($date, $json = false)
{
    if($date && !empty($date)) {
        if($json) {
            $date = new DateTime($date);
        }
        return $date->format('d.m.Y');
    }
    return '';
}

// zakladne prihlasovacie udaje a nastavenia klienta
$apiUrl = 'https://www.finstat.sk/api/';    // URL adresa Finstat API
$apiKey = 'PLEASE_FILL_IN_YOUR_API_KEY';// PLEASE_FILL_IN_YOUR_API_KEY je NEFUNKCNY API kluc. Pre plnu funkcnost API,
// prosim poziadajte o svoj jedinecny kluc na info@finstat.sk.
$privateKey = 'PLEASE_FILL_IN_YOUR_PRIVATE_KEY';// PLEASE_FILL_IN_YOUR_PRIVATE_KEY je NEFUNKCNY API kluc. Pre plnu funkcnost API,
// prosim poziadajte o svoj privatny kluc na info@finstat.sk.
$stationId = 'Api test';                // Identifik�tor stanice, ktor� dopyt vygenerovala.
// M��e by� �ubovoln� re�azec.
$stationName = 'Api test';                // N�zov alebo opis stanice, ktor� dopyt vygenerovala.
// M��e by� �ubovoln� re�azec.
$timeout = 10;                            // D�ka �akania na odozvu zo servera v sekund�ch.
$json =  false;                         // Flag ci ma API vraciat odpoved ako JSON

// inicializacia klienta
$api = new FinstatReportingApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

$file = (isset($_GET['file']) && !empty($_GET['file'])) ? $_GET['file'] : null;
if(!empty($file)) {
    $data = $api->DownloadReportFile($file, $file . ".xlsx");
}
$topics = $api->RequestTopics($json);
if($topics != null) {

    echo "<pre>";
    if(!empty($topics)) {
        echo '<b>Témy: </b><br />';
        echo "<table>";
        echo
            "<tr><th>ID" .
            "</th><th>Názov" .
            "</th><th>Skupina" .
            "</th></tr>";
        foreach($topics as $topic) {
            echo "<tr>".
                    "<td>" . $topic->ID ."</td>".
                    "<td>" . $topic->Name ."</td>".
                    "<td>" . $topic->Group ."</td>".
                    "</tr>";
        }
        echo "</table><br />";
    }
    echo "</pre>";
    foreach($topics as $topic)
    {
        $list = $api->RequestList($topic->ID, $json);
if($list != null) {

    echo "<pre>";
    if(!empty($list)) {
                echo '<b>Subory: ' . $topic->Name . '</b><br />';
        echo "<table>";
        echo
            "<tr><th>ID" .
            "</th><th>Popis" .
            "</th><th>Téma" .
            "</th><th>Skupina" .
            "</th><th>Počet" .
            "</th><th>Dátum" .    
            "</th></tr>";
        foreach($list as $file) {
            echo "<tr>".
                            "<td><a href=\"?file=".$file->FileName. "\">" . $file->FileName. "</a></td>".
                    "<td>" . $file->Description ."</td>".
                    "<td>" . $file->Topic ."</td>".
                    "<td>" . $file->Group ."</td>".
                    "<td>" . $file->Count ."</td>".
                    "<td>" . (($file->Date) ? echoDate($file->Date, $json) : '').  "</td>".
                    "</tr>";
        }
        echo "</table><br />";
    }
    echo "<pre>";
}
    }
}