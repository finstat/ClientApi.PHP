
<?php
require_once('../FinStatApi/FinstatDailyDiffApi.php');
require_once('../FinStat.ViewModel/Diff/DailyDiff.php');
require_once('../FinStat.ViewModel/Diff/DailyDiffList.php');

function echoDate($date, $json = false)
{
    if($date && !empty($date))
    {
        if($json)
        {
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
$stationId = 'Api test';                // Identifikátor stanice, ktorá dopyt vygenerovala.
                                        // Môže by ¾ubovolný reazec.
$stationName = 'Api test';                // Názov alebo opis stanice, ktorá dopyt vygenerovala.
                                        // Môže by ¾ubovolný reazec.
$timeout = 10;                            // Dåžka èakania na odozvu zo servera v sekundách.
$json =  false;                         // Flag ci ma API vraciat odpoved ako JSON

// inicializacia klienta
$api = new FinstatDailyDiffApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

$file = (isset($_GET['file']) && !empty($_GET['file'])) ? $_GET['file'] : null;
if(!empty($file))
{
    $data = $api->DownloadDailyDiffFile($file, $file);
}
$list = $api->RequestListOfDailyDiffs($json);

if($list != null) {

    echo "<pre>";
    echo '<b>Verzia: </b>'.                    $list->Version.'<br />';
    if(!empty($list->Files))
    {
         echo '<b>Subory: </b><br />';
         echo "<table>";
            echo
                "<tr><th>Nazov" .
                "</th><th>Generovane" .
                "</th><th>Velkost" .
                "</th></tr>";
        foreach($list->Files as $file) {
            echo "<tr><td><a href=\"?file=".$file->FileName. "\">" . $file->FileName. "</a></td><td>" . (($file->GeneratedDate) ? echoDate($file->GeneratedDate, $json) : '').  "</td><td>" . $file->FileSize .' bytov</td></tr>';
        }
        echo "</table><br />";
    }
    echo "<pre>";
}