<?php
require_once('FinstatApi/FinstatApi.php');
require_once('FinstatApi/BaseResult.php');
require_once('FinstatApi/DetailResult.php');
require_once('FinstatApi/ExtendedResult.php');
require_once('FinstatApi/UltimateResult.php');
require_once('FinstatApi/AutoCompleteResult.php');

function echoBase($response)
{
    echo "<pre>";
    echo '<b>IČO: </b>'.                    $response->Ico.'<br />';
    echo '<b>Reg. Číslo: </b>'.             $response->RegisterNumberText.'<br />';
    echo '<b>DIČ: </b>'.                    $response->Dic.'<br />';
    echo '<b>IčDPH: </b>'.                  $response->IcDPH.'<br />';
    if($response instanceof UltimateResult)
    {
        echo '<b>OR Odiel: </b>'.                                   $response->ORSection.'<br />';
        echo '<b>OR Vložka: </b>'.                                  $response->ORInsertNo.'<br />';
        echo '<b>Základné imanie: </b>'.                             $response->BasicCapital.'<br />';
        echo '<b>Rozsah splatenia: </b>'.                            $response->PaybackRange.'<br />';
        if(!empty($response->RegistrationCourt))
        {
            echo '<b>Registrovane na: </b>'.                        $response->RegistrationCourt->Name . ', ' . $response->RegistrationCourt->Street . ' ' . $response->RegistrationCourt->StreetNumber.  ", " . $response->RegistrationCourt->ZipCode . ", " . $response->RegistrationCourt->City .  ", " . $response->RegistrationCourt->District .  ", " . $response->RegistrationCourt->Region .  ", " . $response->RegistrationCourt->Country .'<br />';
        }
    }
    if($response instanceof ExtendedResult)
    {
        echo '<b>Detail IČDPH: IČDPH: </b>'.                        $response->IcDphAdditional->IcDph.'<br />';
        echo '<b>Detail IČDPH: Paragraf: </b>'.                     $response->IcDphAdditional->Paragraph.'<br />';
        echo '<b>Detail IČDPH: Dátum detekovania v zozname subjektov, u ktorých nastali dôvody na zrušenie: </b>'.
        (($response->IcDphAdditional->CancelListDetectedDate) ? $response->IcDphAdditional->CancelListDetectedDate->format('d.m.Y') : '').'<br />';
        echo '<b>Detail IČDPH: Dátum detekovania v zozname vymazaných subjektov: </b>'.
        (($response->IcDphAdditional->RemoveListDetectedDate) ? $response->IcDphAdditional->RemoveListDetectedDate->format('d.m.Y') : '').'<br />';
    }
    echo '<b>Názov: </b>'.                  $response->Name.'<br />';
    echo '<b>Ulica: </b>'.                  $response->Street.'<br />';
    echo '<b>Číslo ulice: </b>'.            $response->StreetNumber.'<br />';
    echo '<b>PSČ: </b>'.                    $response->ZipCode.'<br />';
    echo '<b>Mesto: </b>'.                  $response->City.'<br />';
    echo '<b>Okres: </b>'.                  $response->District.'<br />';
    echo '<b>Kraj: </b>'.                   $response->Region.'<br />';
    echo '<b>Štát: </b>'.                   $response->Country.'<br />';
    if($response instanceof ExtendedResult)
    {
        echo '<b>Tel. čisla: </b>'.                                 implode(', ', $response->Phones).'<br />';
        echo '<b>Emaily: </b>'.                                     implode(', ', $response->Emails).'<br />';
    }
    echo '<b>Odvetvie: </b>'.               $response->Activity.'<br />';
    echo '<b>Založená: </b>'.               (($response->Created) ? $response->Created->format('d.m.Y') : '').'<br />';
    echo '<b>Zrušená: </b>'.                (($response->Cancelled) ? $response->Cancelled->format('d.m.Y') : '') .'<br />';
    if($response instanceof UltimateResult)
    {
        echo '<b>Zrušená podľa OR: </b>'.                (($response->ORCancelled) ? $response->ORCancelled->format('d.m.Y') : '') .'<br />';
    }
    if($response instanceof ExtendedResult)
    {
        echo '<b>Právna forma kód: </b>'.                           $response->LegalFormCode.'<br />';
        echo '<b>Právna forma popis: </b>'.                         $response->LegalFormText.'<br />';
        echo '<b>Druh vlastníctva kód: </b>'.                       $response->OwnershipTypeCode.'<br />';
        echo '<b>Druh vlastníctva popis: </b>'.                     $response->OwnershipTypeText.'<br />';
    }
    echo '<b>SK Nace kód: </b>'.            $response->SkNaceCode.'<br />';
    echo '<b>SK Nace popis: </b>'.          $response->SkNaceText.'<br />';
    echo '<b>SK Nace divízia: </b>'.        $response->SkNaceDivision.'<br />';
    if($response instanceof ExtendedResult)
    {
    echo '<b>Príznak, či sa daná firma je živnostník: </b>';
        if($response->SelfEmployed) echo 'Áno <br />'; else echo 'Nie<br />';
    }
    echo '<b>SK Nace skupina: </b>'.        $response->SkNaceGroup.'<br />';
    echo '<b>Pozastavená(živnosť): </b>'.   (($response->SuspendedAsPerson)? "Ano": "Nie").'<br />';
    if($response instanceof ExtendedResult)
    {
        echo '<b>Kód počtu zamestnancov: </b>'.                     $response->EmployeeCode.'<br />';
        echo '<b>Text počtu zamestnancov: </b>'.                    $response->EmployeeText.'<br />';
        echo '<b>Aktuálny rok: </b>'.                               $response->ActualYear.'<br />';
        echo '<b>Credit scoring: </b>'.                             $response->CreditScoreValue.'<br />';
        echo '<b>Credit scoring - text: </b>'.                      $response->CreditScoreState.'<br />';
        echo '<b>Zisk za aktuálny rok: </b>'.                       $response->ProfitActual.'<br />';
        echo '<b>Zisk za predošlý rok: </b>'.                       $response->ProfitPrev.'<br />';
        echo '<b>Suma celkových výnosov za aktuálny rok: </b>'.     $response->RevenueActual.'<br />';
        echo '<b>Suma celkových výnosov za predošlý rok: </b>'.     $response->RevenuePrev.'<br />';
        echo '<b>Pomer cudzích zdrojov za aktuálny rok : </b>'.     $response->ForeignResources.'<br />';
        echo '<b>Hrubá marža za aktuálny rok: </b>'.                $response->GrossMargin.'<br />';
        echo '<b>ROA výnosov za aktuálny rok: </b>'.                $response->ROA.'<br />';
        echo '<b>Posledný dátum zmeny v Konkurzoch a Reštrukturalizáciach: </b>'. (($response->WarningKaR) ? $response->WarningKaR->format('d.m.Y') : '').'<br />';
        echo '<b>Posledný dátum zmeny v Likvidáciach: </b>'.        (($response->WarningLiquidation) ? $response->WarningLiquidation->format('d.m.Y') : '').'<br />';
    }
    echo '<b>Url: </b>'.            $response->Url.'<br />';
    echo '<b>Príznak, či sa daná firma nachádza v zoznamoch dlžníkov, konkurzov alebo likvidácií: </b>';
    if($response->Warning) echo 'Áno (<a href="'.$response->WarningUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';
    echo '<b>Príznak, či má platobné príkazy: </b> ';
    if($response->PaymentOrderWarning) echo 'Áno (<a href="'.$response->PaymentOrderUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';
    echo '<b>Príznak, či nastala pre danú firmu zmena v ORSR počas posledných 3 mesiacov: </b> ';
    if($response->OrChange) echo 'Áno (<a href="'.$response->OrChangeUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';
    if($response instanceof DetailResult)
    {
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
    }
    if($response instanceof ExtendedResult)
    {
        echo '<b>Dlhy: </b><br />';
        if(!empty($response->Debts)) {
            echo "<br /><table>";
            echo
                    "<tr><th>Zdroj" .
                    "</th><th>Hodnota" .
                    "</th><th>Platné od" .
                    "</th></tr>";
            foreach($response->Debts as $debt) {
                echo "<tr><td>" . $debt->Source. "</td><td>" . $debt->Value.  "</td><td>" . (($debt->ValidFrom) ? $debt->ValidFrom->format('d.m.Y') : '') .'</td></tr>';
            }
            echo "</table><br />";
        }
        echo '<b>Platobné rozkazy: </b><br />';
        if(!empty($response->PaymentOrders)) {
            echo "<br /><table>";
            echo
                    "<tr><th>Dátum uverejnenia" .
                    "</th><th>Hodnota" .
                    "</th></tr>";
            foreach($response->PaymentOrders as $paymentOrder) {
                echo "<tr><td>" . (($paymentOrder->PublishDate) ? $paymentOrder->PublishDate->format('d.m.Y') : '') . "</td><td>" . $paymentOrder->Value.  "</td></tr>";
            }
            echo "</table><br />";
        }
        if(!empty($response->Offices)) {
            echo '<b>Prevádzky: </b><br />';
            echo "<br /><table>";
            echo
                    "<tr><th>Addesa" .
                    "</th><th>Predmety podnikania" .
                    "</th><th>Typ".
                    "</th></tr>";
            foreach($response->Offices as $office) {
                echo    "<tr><td>" .
                        $office->Street . " " . $office->StreetNumber . ", ".
                        $office->City . " " . $office->ZipCode . ", ".
                        $office->District . ", " . $office->Region . ", " . $office->Country .
                        "</td><td>" .
                        (!empty($office->Subjects) ? implode(",<br />", $office->Subjects) : "") .
                        "</td><td>" .
                        $office->Type .
                        "</td></tr>";
            }
            echo "</table><br />";
        }
        if (!empty($response->Subjects)) {
            echo '<b>Predmety podnikania: </b><br />';
            echo "<br /><table>";
            echo
                    "<tr><th>Názov" .
                    "</th><th>Od" .
                    "</th><th>Pozastavené od".
                    "</th></tr>";
            foreach($response->Subjects as $subject) {
                echo    "<tr><td>" .
                        $subject->Title .
                        "</td><td>" .
                        (($subject->ValidFrom) ? $subject->ValidFrom->format('d.m.Y') : '').
                        "</td><td>" .
                        (($subject->SuspendedFrom) ? $subject->SuspendedFrom->format('d.m.Y') : '').
                        "</td></tr>";
            }
            echo "</table><br />";
        }
        if ($response->SelfEmployed && !empty($response->StructuredName)) {
            echo '<b>Štrukturovane meno: </b><br />';
            echo (!empty($response->StructuredName->Prefix))   ? "Prefix: " . implode(" ", $response->StructuredName->Prefix) . "<br />": "";
            echo (!empty($response->StructuredName->Name))     ? "Name: " . implode(" ", $response->StructuredName->Name) . "<br />": "";
            echo (!empty($response->StructuredName->Suffix))   ? "Suffix: " . implode(" ", $response->StructuredName->Suffix) . "<br />": "";
            echo (!empty($response->StructuredName->After))    ? "After: " . implode(" ", $response->StructuredName->After) . "<br />": "";
            echo "<br />";
        }
        echo '<br />';
    }
    if($response instanceof UltimateResult)
    {
        if (!empty($response->Persons)) {
            echo '<b>Osoby: </b><br />';
            echo "<br /><table>";
            echo
                "<tr><th>Meno" .
                "</th><th>Adresa" .
                "</th><th>Detekovane od" .
                "</th><th>Detekovane do" .
                "</th><th>Funckcia" .
                "</th><th>Podiel / Vyska splatenia" .
                "</th></tr>";
            foreach ($response->Persons as $person) {
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
                    "</td><td>" . $person->Street ." " . $person->StreetNumber. ", " . $person->ZipCode . ", " . $person->City .  ", " . $person->District .  ", " . $person->Region .  ", " . $person->Country .
                    "</td><td>" . (($person->DetectedFrom) ? $person->DetectedFrom->format('d.m.Y') : '') .
                    "</td><td>" . (($person->DetectedTo) ? $person->DetectedTo->format('d.m.Y') : '') .
                    "</td><td>" . $functions .
                    "</td><td>" . $person->DepositAmount . "/" . $person->PaybackRange .
                    "</td></tr>";
            }
            echo "</table><br />";
        }
        if (!empty($response->ProcurationAction)) {
            echo '<b>Konanie prokúry: </b>' .                   $response->ProcurationAction.'<br />';
        }
        if (!empty($response->StatutoryAction)) {
            echo '<b>Konanie štatutárov: </b>' .                   $response->StatutoryAction.'<br />';
        }
        if (!empty($response->WebPages)) {
            echo '<b>Web stránky: </b>' .                   implode(", ", $response->WebPages).'<br />';
        }
        if (!empty($response->AddressHistory)) {
            echo '<b>História adries: </b><br />';
            echo "<br /><table><tr>";
            echo
                "</th><th>Adresa" .
                "</th><th>Platná od" .
                "</th><th>Platná do" .
                "</th></tr>";
            foreach ($response->AddressHistory as $address) {
                echo
                    "<tr></td><td>" . $address->Street ." " . $address->StreetNumber. ", " . $address->ZipCode . ", " . $address->City .  ", " . $address->District .  ", " . $address->Region .  ", " . $address->Country .
                    "</td><td>" . (($address->ValidFrom) ? $address->ValidFrom->format('d.m.Y') : '') .
                    "</td><td>" . (($address->ValidTo) ? $address->ValidTo->format('d.m.Y') : '') .
                    "</td></tr>";
            }
            echo "</table><br />";
        }

        if(!empty($response->Bankrupt) || !empty($response->Restructuring) || !empty($response->Liquidation)) {
            echo '<b>Konkurz / Reštruktualizácia / Likvidácia: </b><br />';
            echo "<br /><table><tr>";
            echo "<tr>";
            echo
                "</th><th>" .
                "</th><th>Dátum vstupu" .
                "</th><th>" .
                "</th><th>Dátum výstupu" .
                "</th><th>" .
                "</th><th> Správca" .
                "</th></tr>";
            if(!empty($response->Bankrupt)) {
                echo "<tr><th>Konkurz</th></td><td>".
                    (($response->Bankrupt->EnterDate) ? $response->Bankrupt->EnterDate->format('d.m.Y') : '') ."</td><td>".
                    $response->Bankrupt->EnterReason."</td><td>".
                    (($response->Bankrupt->ExitDate) ? $response->Bankrupt->ExitDate->format('d.m.Y') : '') ."</td><td>".
                    $response->Bankrupt->ExitReason."</td><td>".
                    (($response->Bankrupt->Officer) ? $response->Bankrupt->Officer->FullName : '')."</td><td>".
                    "</td></tr>";
            }
            if(!empty($response->Restructuring)) {
                echo "<tr><th>Reštrukturalizácia</th></td><td>".
                    (($response->Restructuring->EnterDate) ? $response->Restructuring->EnterDate->format('d.m.Y') : '') ."</td><td>".
                    $response->Restructuring->EnterReason."</td><td>".
                    (($response->Restructuring->ExitDate) ? $response->Restructuring->ExitDate->format('d.m.Y') : '') ."</td><td>".
                    $response->Restructuring->ExitReason."</td><td>".
                    (($response->Restructuring->Officer) ? $response->Restructuring->Officer->FullName : '')."</td><td>".
                    "</td></tr>";
            }
            if(!empty($response->Liquidation)) {
                echo "<tr><th>Likvidácia</th></td><td>".
                    (($response->Liquidation->EnterDate) ? $response->Liquidation->EnterDate->format('d.m.Y') : '') ."</td><td>".
                    $response->Liquidation->EnterReason."</td><td>".
                    (($response->Liquidation->ExitDate) ? $response->Liquidation->ExitDate->format('d.m.Y') : '') ."</td><td>".
                    "</td><td>".
                    (($response->Liquidation->Officer) ? $response->Liquidation->Officer->FullName : '')."</td><td>".
                    "</td></tr>";
            }
            echo "</table><br />";
        }
    }
    echo "</pre>";
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

function echoAutoComplete($response)
{
    echo "<pre>";
    echo '<b>Výsledky: </b><br />';
    if (!empty($response->Results)) {
        echo "<table>";
        echo
            "<tr><th>ICO" .
            "</td><th>Nazov" .
            "</td><th>Mesto" .
            "</td><th>Zrusena" .
            "</th></tr>"
        ;
        foreach ($response->Results as $company) {
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
    if (!empty($response->Suggestions)) {
        echo implode(', ', $response->Suggestions);
    }
    echo '<br />';
    echo '<hr />';
    echo "</pre>";
}

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
$ico = (isset($_GET['ico']) && !empty($_GET['ico'])) ? $_GET['ico'] : '35757442';
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
    echoException($e);
}

// priklad vypisu ziskanych udajov z Finstatu
header('Content-Type: text/html; charset=utf-8');
echoBase($response);
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
    echoException($e);
}
echoBase($response2);
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
    echoException($e);
}
echoBase($response3);
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
    echoException($e);
}
echoAutoComplete($response4);
