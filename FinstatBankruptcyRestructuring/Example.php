
<?php
require_once(__DIR__ . '/../FinStatApi/FinstatBankruptcyRestructuringApi.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Deadline.php');
require_once(__DIR__ . '/../FinStat.ViewModel/BankuptcyRestructuing/BankruptcyRestructuring.php');

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

function echoBankruptcyRestructuringList($response, $json = false)
{
    echo '<table>';
    foreach($response as $data) {
        echo '<tr>';
        echo '<td>';
        foreach($data->Debtors as $debtot) {
            echo $debtot->Name . '<br />';
        } 
        echo'</td>';
        echo '<td>' . $data->FileReference . '</td>';
        echo '<td>' . echoDate($data->FirstRecordDate, $json) . '</td>';
        echo '<td>' . echoDate($data->LastRecordDate, $json) . '</td>';
        echo '<td>' . $data->RUState . " " . echoDate($data->RUStateDate, $json) . '</td>';
        echo '<td>' . $data->OVState . " " . echoDate($data->OVStateDate, $json) . '</td>';
        echo '<td>' . echoDate($data->EnterDate, $json) . '</td>';
        echo '<td>' . echoDate($data->ExitDate, $json) . '</td>';
        echo '<td>' . $data->EndState . '</td>';
        echo '<td>' . $data->EndReason . '</td>';
        echo '<td>';
        foreach($data->Deadlines as $deadline) {
            echo $deadline->Type . " " . echoDate($deadline->Date, $json). '<br />';
        } 
        echo'</td>';
        echo '<td>' . $data->FinstatURL . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<hr />';
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
$api = new FinstatBankruptcyRestructuringApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

$data = $api->RequestPersonBankruptcyProceedings("peter", "toth", new \DateTime("1988-08-16") , $json);
echoBankruptcyRestructuringList($data, $json);
$data = $api->RequestCompanyBankruptcyRestructuring("36381250", null, $json);
echoBankruptcyRestructuringList($data, $json);
$data = $api->RequestCompanyBankruptcyRestructuring(null, "talise" , $json);
echoBankruptcyRestructuringList($data, $json);