<?php
require_once('FinstatApi/FinstatApi.php');

// zakladne prihlasovacie udaje a nastavenia klienta
$apiUrl = 'http://www.finstat.sk/api/';    // URL adresa Finstat API
$apiKey = 'PLEASE_FILL_IN_YOUR_API_KEY';// PLEASE_FILL_IN_YOUR_API_KEY je NEFUNKCNY API kluc. Pre plnu funkcnost API,
                                        // prosim poziadajte o svoj jedinecny kluc na info@finstat.sk.
$privateKey = 'PLEASE_FILL_IN_YOUR_PRIVATE_KEY';// PLEASE_FILL_IN_YOUR_PRIVATE_KEY je NEFUNKCNY API kluc. Pre plnu funkcnost API,
                                        // prosim poziadajte o svoj privatny kluc na info@finstat.sk.
$stationId = 'Api test';                // Identifikátor stanice, ktorá dopyt vygenerovala.
                                        // Môže byť ľubovolný reťazec.
$stationName = 'Api test';                // Názov alebo opis stanice, ktorá dopyt vygenerovala.
                                        // Môže byť ľubovolný reťazec.
$timeout = 10;                            // Dĺžka čakania na odozvu zo servera v sekundách.

// inicializacia klienta
$api = new FinstatApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

// priklad dopytu na detail firmy, ktora ma ICO 35757442
$ico = ($_GET['ico']) ? $_GET['ico'] : '35757442';
?>
<h1>Detail test:</h1>
<?php
try
{
    // funkcia $api->RequestDetail(string) vracia naplneny objekt typu DetailResult s udajmi o dopytovanej firme
    if (!empty($ico)) {
        $response = $api->Request($ico);
    }
}
catch (Exception $e)
{
    // popis a kod chyby, ktora nastala
    throw new Exception("Load Fails with exception code: " . $e->getCode() . " and message: " . $e->getMessage());
}

// priklad vypisu ziskanych udajov z Finstatu
header('Content-Type: text/html; charset=utf-8');
echo "<pre>";
echo '<b>IČO: </b>'.                    $response->Ico.'<br />';
echo '<b>Reg. Číslo: </b>'.             $response->RegisterNumberText.'<br />';
echo '<b>DIČ: </b>'.                    $response->Dic.'<br />';
echo '<b>IčDPH: </b>'.                  $response->IcDPH.'<br />';
echo '<b>Názov: </b>'.                  $response->Name.'<br />';
echo '<b>Ulica: </b>'.                  $response->Street.'<br />';
echo '<b>Číslo ulice: </b>'.            $response->StreetNumber.'<br />';
echo '<b>PSČ: </b>'.                    $response->ZipCode.'<br />';
echo '<b>Mesto: </b>'.                  $response->City.'<br />';
echo '<b>Odvetvie: </b>'.               $response->Activity.'<br />';
echo '<b>Založená: </b>'.               (($response->Created) ? $response->Created->format('d.m.Y') : '').'<br />';
echo '<b>Zrušená: </b>'.                (($response->Cancelled) ? $response->Cancelled->format('d.m.Y') : '') .'<br />';
echo '<b>Pozastavená(živnosť): </b>'.   (($response->SuspendedAsPerson)? "Ano": "Nie").'<br />';
echo '<b>Url: </b>'.            $response->Url.'<br />';
echo '<b>Príznak, či sa daná firma nachádza v zoznamoch dlžníkov, konkurzov alebo likvidácií: </b>';
if($response->Warning) echo 'Áno (<a href="'.$response->WarningUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';

echo '<b>Príznak, či má platobné príkazy: </b> ';
if($response->PaymentOrderWarning) echo 'Áno (<a href="'.$response->PaymentOrderUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';

echo '<b>Príznak, či nastala pre danú firmu zmena v ORSR počas posledných 3 mesiacov: </b> ';
if($response->OrChange) echo 'Áno (<a href="'.$response->OrChangeUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';

echo '<b>Príznak nárastu/poklesu tržieb firmy medzi posledným a predposledným rokom v databáze: </b>';
switch($response->Revenue)
{
    case 'Unknown': echo 'Neznámy'; break;
    case 'Up': echo 'Nárast (<a href="'.$response->Url.'">viac info</a>)'; break;
    case 'Down': echo 'Pokles (<a href="'.$response->Url.'">viac info</a>)'; break;
}
echo '<br />';

echo '<b>Príznak nárastu/poklesu zisku firmy medzi posledným a predposledným rokom v databáze: </b>';
switch($response->Profit)
{
    case 'Unknown': echo 'Neznámy'; break;
    case 'Up': echo 'Nárast (<a href="'.$response->Url.'">viac info</a>)'; break;
    case 'Down': echo 'Pokles (<a href="'.$response->Url.'">viac info</a>)'; break;
    case 'Loss': echo 'Firma bola posledný rok v strate (<a href="'.$response->Url.'">viac info</a>)'; break;
}
echo '<br />';
echo "</pre>";
echo '<hr />';
?>
<h1>Extended test:</h1>
<?php
try
{
    // funkcia $api->RequestExtended(string) vracia naplneny objekt typu ExtendedResult s udajmi o dopytovanej firme
    if (!empty($ico)) {
        $response2 = $api->Request($ico, 'extended');
    }
}
catch (Exception $e)
{
    // popis a kod chyby, ktora nastala
    throw new Exception("Load Fails with exception code: " . $e->getCode() . " and message: " . $e->getMessage());
}
echo "<pre>";
echo '<b>IČO: </b>'.                                        $response2->Ico.'<br />';
echo '<b>Reg. Číslo: </b>'.                                 $response2->RegisterNumberText.'<br />';
echo '<b>DIČ: </b>'.                                        $response2->Dic.'<br />';
echo '<b>IČDPH: </b>'.                                      $response2->IcDPH.'<br />';
echo '<b>Detail IČDPH: IČDPH: </b>'.                        $response2->IcDphAdditional->IcDph.'<br />';
echo '<b>Detail IČDPH: Paragraf: </b>'.                     $response2->IcDphAdditional->Paragraph.'<br />';
echo '<b>Detail IČDPH: Dátum detekovania v zozname subjektov, u ktorých nastali dôvody na zrušenie: </b>'.
  (($response2->IcDphAdditional->CancelListDetectedDate) ? $response2->IcDphAdditional->CancelListDetectedDate->format('d.m.Y') : '').'<br />';
echo '<b>Detail IČDPH: Dátum detekovania v zozname vymazaných subjektov: </b>'.
  (($response2->IcDphAdditional->RemoveListDetectedDate) ? $response2->IcDphAdditional->RemoveListDetectedDate->format('d.m.Y') : '').'<br />';
echo '<b>Názov: </b>'.                                      $response2->Name.'<br />';
echo '<b>Ulica: </b>'.                                      $response2->Street.'<br />';
echo '<b>Číslo ulice: </b>'.                                $response2->StreetNumber.'<br />';
echo '<b>PSČ: </b>'.                                        $response2->ZipCode.'<br />';
echo '<b>Mesto: </b>'.                                      $response2->City.'<br />';
echo '<b>Okres: </b>'.                                      $response2->District.'<br />';
echo '<b>Kraj: </b>'.                                       $response2->Region.'<br />';
echo '<b>Odvetvie: </b>'.                                   $response2->Activity.'<br />';
echo '<b>Založená: </b>'.                                   (($response2->Created) ? $response2->Created->format('d.m.Y') : '').'<br />';
echo '<b>Zrušená: </b>'.                                    (($response2->Cancelled) ? $response2->Cancelled->format('d.m.Y') : '') .'<br />';
echo '<b>Tel. čisla: </b>'.                                 implode(', ', $response2->Phones).'<br />';
echo '<b>Emaily: </b>'.                                     implode(', ', $response2->Emails).'<br />';
echo '<b>Právna forma kód: </b>'.                           $response2->LegalFormCode.'<br />';
echo '<b>Právna forma popis: </b>'.                         $response2->LegalFormText.'<br />';
echo '<b>Druh vlastníctva kód: </b>'.                       $response2->OwnershipTypeCode.'<br />';
echo '<b>Druh vlastníctva popis: </b>'.                     $response2->OwnershipTypeText.'<br />';
echo '<b>SK Nace kód: </b>'.                                $response2->SkNaceCode.'<br />';
echo '<b>SK Nace popis: </b>'.                              $response2->SkNaceText.'<br />';
echo '<b>SK Nace divízia: </b>'.                            $response2->SkNaceDivision.'<br />';
echo '<b>SK Nace skupina: </b>'.                            $response2->SkNaceGroup.'<br />';
echo '<b>Kód počtu zamestnancov: </b>'.                     $response2->EmployeeCode.'<br />';
echo '<b>Text počtu zamestnancov: </b>'.                    $response2->EmployeeText.'<br />';
echo '<b>Aktuálny rok: </b>'.                               $response2->ActualYear.'<br />';
echo '<b>Credit scoring: </b>'.                             $response2->CreditScoreValue.'<br />';
echo '<b>Credit scoring - text: </b>'.                      $response2->CreditScoreState.'<br />';
echo '<b>Zisk za aktuálny rok: </b>'.                       $response2->ProfitActual.'<br />';
echo '<b>Zisk za predošlý rok: </b>'.                       $response2->ProfitPrev.'<br />';
echo '<b>Suma celkových výnosov za aktuálny rok: </b>'.     $response2->RevenueActual.'<br />';
echo '<b>Suma celkových výnosov za predošlý rok: </b>'.     $response2->RevnuePrev.'<br />';
echo '<b>Pomer cudzích zdrojov za aktuálny rok : </b>'.     $response2->ForeignResources.'<br />';
echo '<b>Hrubá marža za aktuálny rok: </b>'.                $response2->GrossMargin.'<br />';
echo '<b>ROA výnosov za aktuálny rok: </b>'.                $response2->ROA.'<br />';
echo '<b>Posledný dátum zmeny v Konkurzoch a Reštrukturalizáciach: </b>'.
                                                            (($response2->WarningKaR) ? $response2->WarningKaR->format('d.m.Y') : '').'<br />';
echo '<b>Posledný dátum zmeny v Likvidáciach: </b>'.        (($response2->WarningLiquidation) ? $response2->WarningLiquidation->format('d.m.Y') : '').'<br />';
echo '<b>Url: </b>'.                                        $response2->Url.'<br />';
echo '<b>Dlhy: </b><br />';
if(!empty($response2->Debts)) {
    echo "<br /><table>";
    echo
            "<tr><th>Zdroj" .
            "</th><th>Hodnota" .
            "</th><th>Platné od" .
            "</th></tr>";
    foreach($response2->Debts as $debt) {
        echo "<tr><td>" . $debt->Source. "</td><td>" . $debt->Value.  "</td><td>" . (($debt->ValidFrom) ? $debt->ValidFrom->format('d.m.Y') : '') .'</td></tr>';
    }
    echo "</table><br />";
}
echo '<b>Platobné rozkazy: </b><br />';
if(!empty($response2->PaymentOrders)) {
    echo "<br /><table>";
    echo
            "<tr><th>Dátum uverejnenia" .
            "</th><th>Hodnota" .
            "</th></tr>";
    foreach($response2->PaymentOrders as $paymentOrder) {
        echo "<tr><td>" . (($paymentOrder->PublishDate) ? $paymentOrder->PublishDate->format('d.m.Y') : '') . "</td><td>" . $paymentOrder->Value.  "</td></tr>";
    }
    echo "</table><br />";
}
echo '<b>Príznak, či sa daná firma nachádza v zoznamoch dlžníkov, konkurzov alebo likvidácií: </b>';
if($response2->Warning) echo 'Áno (<a href="'.$response2->WarningUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';

echo '<b>Príznak, či má platobné príkazy: </b> ';
if($response->PaymentOrderWarning) echo 'Áno (<a href="'.$response->PaymentOrderUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';

echo '<b>Príznak, či nastala pre danú firmu zmena v ORSR počas posledných 3 mesiacov: </b> ';
if($response2->OrChange) echo 'Áno (<a href="'.$response2->OrChangeUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';

echo '<b>Príznak, či sa daná firma je živnostník: </b>';
if($response2->SelfEmployed) echo 'Áno <br />'; else echo 'Nie<br />';
if(!empty($response2->Offices)) {
    echo '<b>Prevádzky: </b><br />';
    echo "<br /><table>";
    echo
            "<tr><th>Addesa" .
            "</th><th>Predmety podnikania" .
            "</th><th>Typ".
            "</th></tr>";
    foreach($response2->Offices as $office) {
        echo    "<tr><td>" .´
                $office->Street . " " . $office->StreetNumber . ", ". 
                $office->City . " " . $office->ZipCode . ", ".
                $office->District . ", " . $office->Region . ", " . $office->Country,
                "</td><td>" . 
                (!empty($office->Subjects)) ? implode(",<br />", $office->Subjects) : "" .
                "</td><td>" . 
                $office->Type .  
                "</td></tr>";
    }
    echo "</table><br />";
}
if (!empty($response2->Subjects)) {
    echo '<b>Predmety podnikania: </b><br />';  
    echo "<br /><table>";
    echo
            "<tr><th>Názov" .
            "</th><th>Od" .
            "</th><th>Pozastavené od".
            "</th></tr>";
    foreach($response2->Subjects as $subject) {
        echo    "<tr><td>" .´
                $subject->Title .
                "</td><td>" . 
                (($subject->ValidFrom) ? $subject->ValidFrom->format('d.m.Y') : '').
                "</td><td>" . 
                (($subject->SuspendedFrom) ? $subject->SuspendedFrom->format('d.m.Y') : '').
                "</td></tr>";
    }
    echo "</table><br />";  
}
echo '<br />';
echo "</pre>";
echo '<hr />';
?>
<h1>Ultimate test:</h1>
<?php
try
{
    // funkcia $api->RequestExtended(string) vracia naplneny objekt typu ExtendedResult s udajmi o dopytovanej firme
    if (!empty($ico)) {
        $response3 = $api->Request($ico, 'ultimate');
    }
}
catch (Exception $e)
{
    // popis a kod chyby, ktora nastala
    throw new Exception("Load Fails with exception code: " . $e->getCode() . " and message: " . $e->getMessage());
}
echo "<pre>";
echo '<b>IČO: </b>'.                                        $response3->Ico.'<br />';
echo '<b>Reg. Číslo: </b>'.                                 $response3->RegisterNumberText.'<br />';
echo '<b>DIČ: </b>'.                                        $response3->Dic.'<br />';
echo '<b>IČDPH: </b>'.                                      $response3->IcDPH.'<br />';
echo '<b>Detail IČDPH: IČDPH: </b>'.                        $response3->IcDphAdditional->IcDph.'<br />';
echo '<b>Detail IČDPH: Paragraf: </b>'.                     $response3->IcDphAdditional->Paragraph.'<br />';
echo '<b>Detail IČDPH: Dátum detekovania v zozname subjektov, u ktorých nastali dôvody na zrušenie: </b>'.
  (($response3->IcDphAdditional->CancelListDetectedDate) ? $response3->IcDphAdditional->CancelListDetectedDate->format('d.m.Y') : '').'<br />';
echo '<b>Detail IČDPH: Dátum detekovania v zozname vymazaných subjektov: </b>'.
  (($response3->IcDphAdditional->RemoveListDetectedDate) ? $response3->IcDphAdditional->RemoveListDetectedDate->format('d.m.Y') : '').'<br />';
echo '<b>Názov: </b>'.                                      $response3->Name.'<br />';
echo '<b>Ulica: </b>'.                                      $response3->Street.'<br />';
echo '<b>Číslo ulice: </b>'.                                $response3->StreetNumber.'<br />';
echo '<b>PSČ: </b>'.                                        $response3->ZipCode.'<br />';
echo '<b>Mesto: </b>'.                                      $response3->City.'<br />';
echo '<b>Okres: </b>'.                                      $response3->District.'<br />';
echo '<b>Kraj: </b>'.                                       $response3->Region.'<br />';
echo '<b>Odvetvie: </b>'.                                   $response3->Activity.'<br />';
echo '<b>Založená: </b>'.                                   (($response3->Created) ? $response3->Created->format('d.m.Y') : '').'<br />';
echo '<b>Zrušená: </b>'.                                    (($response3->Cancelled) ? $response3->Cancelled->format('d.m.Y') : '') .'<br />';
echo '<b>Tel. čisla: </b>'.                                 implode(', ', $response3->Phones).'<br />';
echo '<b>Emaily: </b>'.                                     implode(', ', $response3->Emails).'<br />';
echo '<b>OR Odiel: </b>'.                                   $response3->ORSection.'<br />';
echo '<b>OR Vložka: </b>'.                                  $response3->ORInsertNo.'<br />';
echo '<b>Právna forma kód: </b>'.                           $response3->LegalFormCode.'<br />';
echo '<b>Právna forma popis: </b>'.                         $response3->LegalFormText.'<br />';
echo '<b>Druh vlastníctva kód: </b>'.                       $response3->OwnershipTypeCode.'<br />';
echo '<b>Druh vlastníctva popis: </b>'.                     $response3->OwnershipTypeText.'<br />';
echo '<b>SK Nace kód: </b>'.                                $response3->SkNaceCode.'<br />';
echo '<b>SK Nace popis: </b>'.                              $response3->SkNaceText.'<br />';
echo '<b>SK Nace divízia: </b>'.                            $response3->SkNaceDivision.'<br />';
echo '<b>SK Nace skupina: </b>'.                            $response3->SkNaceGroup.'<br />';
echo '<b>Kód počtu zamestnancov: </b>'.                     $response3->EmployeeCode.'<br />';
echo '<b>Text počtu zamestnancov: </b>'.                    $response3->EmployeeText.'<br />';
echo '<b>Aktuálny rok: </b>'.                               $response3->ActualYear.'<br />';
echo '<b>Credit scoring: </b>'.                             $response3->CreditScoreValue.'<br />';
echo '<b>Credit scoring - text: </b>'.                      $response3->CreditScoreState.'<br />';
echo '<b>Zisk za aktuálny rok: </b>'.                       $response3->ProfitActual.'<br />';
echo '<b>Zisk za predošlý rok: </b>'.                       $response3->ProfitPrev.'<br />';
echo '<b>Suma celkových výnosov za aktuálny rok: </b>'.     $response3->RevenueActual.'<br />';
echo '<b>Suma celkových výnosov za predošlý rok: </b>'.     $response3->RevnuePrev.'<br />';
echo '<b>Pomer cudzích zdrojov za aktuálny rok : </b>'.     $response3->ForeignResources.'<br />';
echo '<b>Hrubá marža za aktuálny rok: </b>'.                $response3->GrossMargin.'<br />';
echo '<b>ROA výnosov za aktuálny rok: </b>'.                $response3->ROA.'<br />';
echo '<b>Posledný dátum zmeny v Konkurzoch a Reštrukturalizáciach: </b>'.
                                                            (($response3->WarningKaR) ? $response3->WarningKaR->format('d.m.Y') : '').'<br />';
echo '<b>Posledný dátum zmeny v Likvidáciach: </b>'.        (($response3->WarningLiquidation) ? $response3->WarningLiquidation->format('d.m.Y') : '').'<br />';
echo '<b>Url: </b>'.                                        $response3->Url.'<br />';
echo '<b>Dlhy: </b><br />';
if (!empty($response3->Debts)) {
    echo "<br /><table>";
    echo
            "<tr><th>Zdroj" .
            "</th><th>Hodnota" .
            "</th><th>Platné od" .
            "</th></tr>";
    foreach ($response3->Debts as $debt) {
        echo
            "<tr><td>" . $debt->Source .
            "</td><td>" . $debt->Value .
            "</td><td>" . (($debt->ValidFrom) ? $debt->ValidFrom->format('d.m.Y') : '') .
            "</td></tr>"
        ;
    }
    echo "</table><br />";
}
echo '<b>Platobné rozkazy: </b><br />';
if(!empty($response2->PaymentOrders)) {
    echo "<br /><table>";
    echo
            "<tr><th>Dátum uverejnenia" .
            "</th><th>Hodnota" .
            "</th></tr>";
    foreach($response2->PaymentOrders as $paymentOrder) {
        echo "<tr><td>" . (($paymentOrder->PublishDate) ? $paymentOrder->PublishDate->format('d.m.Y') : '') . "</td><td>" . $paymentOrder->Value.  "</td></tr>";
    }
    echo "</table><br />";
}
echo '<b>Osoby: </b><br />';
if (!empty($response3->Persons)) {
    echo "<br /><table>";
    echo
        "<tr><th>Meno" .
        "</th><th>Ulica" .
        "</th><th>Cislo" .
        "</th><th>PSC" .
        "</th><th>Mesto" .
        "</th><th>Detekovane od" .
        "</th><th>Detekovane do" .
        "</th><th>Funckcia" .
        "</th></tr>";
    foreach ($response3->Persons as $person) {
        $functions = "";
        if (!empty($person->Functions)) {
            foreach ($person->Functions as $function) {
                $functions .= $function->Type . " - ";
                $functions .= $function->Description;
                if ($function->From) {
                    $functions .= " (" . $function->From->format('d.m.Y') . ")";
                }
                $functions .="<br />";
            }
        }
        echo
            "<tr><td>" . $person->FullName .
            "</td><td>" . $person->Street .
            "</td><td>" . $person->StreetNumber.
            "</td><td>" . $person->ZipCode .
            "</td><td>" . $person->City .
            "</td><td>" . (($person->DetectedFrom) ? $person->DetectedFrom->format('d.m.Y') : '') .
            "</td><td>" . (($person->DetectedTo) ? $person->DetectedTo->format('d.m.Y') : '') .
            "</td><td>" . $functions .
            "</td></tr>";
    }
    echo "</table><br />";
}
echo '<b>Príznak, či sa daná firma nachádza v zoznamoch dlžníkov, konkurzov alebo likvidácií: </b>';
if($response3->Warning) echo 'Áno (<a href="'.$response3->WarningUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';

echo '<b>Príznak, či má platobné príkazy: </b> ';
if($response->PaymentOrderWarning) echo 'Áno (<a href="'.$response->PaymentOrderUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';

echo '<b>Príznak, či nastala pre danú firmu zmena v ORSR počas posledných 3 mesiacov: </b> ';
if($response3->OrChange) echo 'Áno (<a href="'.$response3->OrChangeUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';
echo '<b>Príznak, či sa daná firma je živnostník: </b>';
if($response3->SelfEmployed) echo 'Áno <br />'; else echo 'Nie<br />';
if(!empty($response3->Offices)) {
    echo '<b>Prevádzky: </b><br />';
    echo "<br /><table>";
    echo
            "<tr><th>Addesa" .
            "</th><th>Predmety podnikania" .
            "</th><th>Typ".
            "</th></tr>";
    foreach($response3->Offices as $office) {
        echo "<tr><td>" .´
              $office->Street . " " . $office->StreetNumber . ", ". 
              $office->City . " " . $office->ZipCode . ", ".
              $office->District . ", " . $office->Region . ", " . $office->Country,
        "</td><td>" . 
        (!empty($office->Subjects)) ? implode(",<br />", $office->Subjects) : "" .
        "</td><td>" . 
         $office->Type .  
        "</td></tr>";
    }
    echo "</table><br />";
}
if (!empty($response3->Subjects)) {
    echo '<b>Predmety podnikania: </b><br />';  
    echo "<br /><table>";
    echo
            "<tr><th>Názov" .
            "</th><th>Od" .
            "</th><th>Pozastavené od".
            "</th></tr>";
    foreach($response3->Subjects as $subject) {
        echo    "<tr><td>" .´
                $subject->Title .
                "</td><td>" . 
                (($subject->ValidFrom) ? $subject->ValidFrom->format('d.m.Y') : '').
                "</td><td>" . 
                (($subject->SuspendedFrom) ? $subject->SuspendedFrom->format('d.m.Y') : '').
                "</td></tr>";
    }
    echo "</table><br />";  
}
echo '<br />';
echo "</pre>";
echo '<hr />';
?>
<h1>AutoComplete test "volkswagen":</h1>
<?php
try
{
    $response4 = $api->RequestAutoComplete('volkswagen');
}
catch (Exception $e)
{
    // popis a kod chyby, ktora nastala
    throw new Exception("Load Fails with exception code: " . $e->getCode() . " and message: " . $e->getMessage());
}
echo "<pre>";
echo '<b>Výsledky: </b><br />';
if (!empty($response4->Results)) {
    echo "<table>";
    echo
            "<tr><th>ICO" .
            "</td><th>Nazov" .
            "</td><th>Mesto" .
            "</td><th>Zrusena" .
            "</th></tr>"
        ;
    foreach ($response4->Results as $company) {
        echo
            "<tr><td>" . $company->Ico .
            "</td><td>" . $company->Name .
            "</td><td>" . $company->City .
            "</td><td>" . (($company->Cancelled) ? "true" : 'false') .
            "</td></tr>"
        ;
    }
    echo "</table>";
}
echo '<br /><b>Návrhy: </b>';
if (!empty($response4->Suggestions)) {
    echo implode(', ', $response4->Suggestions);
}
echo '<br />';
echo '<hr />';
echo "</pre>";
