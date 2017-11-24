<?php
require_once('FinstatApi/FinstatApi.php');
require_once('FinstatApi/BaseResult.php');
require_once('FinstatApi/DetailResult.php');
require_once('FinstatApi/ExtendedResult.php');
require_once('FinstatApi/UltimateResult.php');
require_once('FinstatApi/AutoCompleteResult.php');

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

function echoStructuredName($data)
{
    $result ="";
    if($data && !empty($data))
    {
        $result = '<b>Štrukturovane meno: </b><br />' .
        ((!empty($data->Prefix))   ? "Prefix: " .   implode(" ", $data->Prefix) . "<br />": "") .
        ((!empty($data->Name))     ? "Name: " .     implode(" ", $data->Name) . "<br />": "" ).
        ((!empty($data->Suffix))   ? "Suffix: " .   implode(" ", $data->Suffix) . "<br />": "" ).
        ((!empty($data->After))    ? "After: " .    implode(" ", $data->After) . "<br />": "" );
    }
    return $result;
}


function echoBase($response, $json = false)
{
    echo "<pre>";
    echo '<b>IČO: </b>'.                    $response->Ico.'<br />';
    echo '<b>Reg. Číslo: </b>'.             $response->RegisterNumberText.'<br />';
    echo '<b>DIČ: </b>'.                    $response->Dic.'<br />';
    echo '<b>IčDPH: </b>'.                  $response->IcDPH.'<br />';
    if($response instanceof ExtendedResult)
    {
        echo '<b>Základné imanie: </b>'.                             $response->BasicCapital.'<br />';
        if($response instanceof UltimateResult || isset($response->ORSection))
        {
            echo '<b>OR Odiel: </b>'.                                   $response->ORSection.'<br />';
            echo '<b>OR Vložka: </b>'.                                  $response->ORInsertNo.'<br />';
            echo '<b>Rozsah splatenia: </b>'.                            $response->PaybackRange.'<br />';
            if(!empty($response->RegistrationCourt))
            {
                echo '<b>Registrovane na: </b>'.                        $response->RegistrationCourt->Name . ', ' . $response->RegistrationCourt->Street . ' ' . $response->RegistrationCourt->StreetNumber.  ", " . $response->RegistrationCourt->ZipCode . ", " . $response->RegistrationCourt->City .  ", " . $response->RegistrationCourt->District .  ", " . $response->RegistrationCourt->Region .  ", " . $response->RegistrationCourt->Country .'<br />';
            }
        }
    }
    echo '<b>Detail IČDPH: IČDPH: </b>'.                         (!empty($response->IcDphAdditional) ? $response->IcDphAdditional->IcDph  : '') .'<br />';
    echo '<b>Detail IČDPH: Paragraf: </b>'.                      (!empty($response->IcDphAdditional) ?$response->IcDphAdditional->Paragraph  : '') .'<br />';
    echo '<b>Detail IČDPH: Dátum detekovania v zozname subjektov, u ktorých nastali dôvody na zrušenie: </b>'.
    (!empty($response->IcDphAdditional) && ($response->IcDphAdditional->CancelListDetectedDate) ? echoDate($response->IcDphAdditional->CancelListDetectedDate, $json) : '').'<br />';
    echo '<b>Detail IČDPH: Dátum detekovania v zozname vymazaných subjektov: </b>'.
    (!empty($response->IcDphAdditional) && ($response->IcDphAdditional->RemoveListDetectedDate) ? echoDate($response->IcDphAdditional->RemoveListDetectedDate, $json) : '').'<br />';
    echo '<b>Rpvs: </b>'.                   $response->RpvsInsert. ' '. $response->RpvsUrl .'<br />';
    echo '<b>Názov: </b>'.                  $response->Name.'<br />';
    echo '<b>Ulica: </b>'.                  $response->Street.'<br />';
    echo '<b>Číslo ulice: </b>'.            $response->StreetNumber.'<br />';
    echo '<b>PSČ: </b>'.                    $response->ZipCode.'<br />';
    echo '<b>Mesto: </b>'.                  $response->City.'<br />';
    echo '<b>Okres: </b>'.                  $response->District.'<br />';
    echo '<b>Kraj: </b>'.                   $response->Region.'<br />';
    echo '<b>Štát: </b>'.                   $response->Country.'<br />';
    echo '<b>Kategoria Tržieb: </b>'.       $response->SalesCategory.'<br />';
    if($response instanceof ExtendedResult|| isset($response->ActualYear))
    {
        echo '<b>Tel. čisla: </b>'.                                 implode(', ', $response->Phones).'<br />';
        echo '<b>Emaily: </b>'.                                     implode(', ', $response->Emails).'<br />';
        if(!empty($response->ContactSources))
        {
            echo '<b>Zdroje:</b> <br />';
            foreach($response->ContactSources as $source) {
                echo $source->Contact . ' -'. (!empty($source->Sources) ? implode(', ', $source->Sources): '' ) .'<br />';
            }
        }

    }
    echo '<b>Odvetvie: </b>'.               $response->Activity.'<br />';
    echo '<b>Založená: </b>'.               (($response->Created) ? echoDate($response->Created, $json) : '').'<br />';
    echo '<b>Zrušená: </b>'.                (($response->Cancelled) ? echoDate($response->Cancelled, $json) : '') .'<br />';
    if($response instanceof UltimateResult || isset($response->ORSection))
    {
        echo '<b>Zrušená podľa OR: </b>'.                (($response->ORCancelled) ? echoDate($response->ORCancelled, $json) : '') .'<br />';
    }
    echo '<b>Právna forma kód: </b>'.                           $response->LegalFormCode.'<br />';
        echo '<b>Právna forma popis: </b>'.                         $response->LegalFormText.'<br />';
    if($response instanceof ExtendedResult|| isset($response->ActualYear))
    {
        echo '<b>Druh vlastníctva kód: </b>'.                       $response->OwnershipTypeCode.'<br />';
        echo '<b>Druh vlastníctva popis: </b>'.                     $response->OwnershipTypeText.'<br />';
    }
    echo '<b>SK Nace kód: </b>'.            $response->SkNaceCode.'<br />';
    echo '<b>SK Nace popis: </b>'.          $response->SkNaceText.'<br />';
    echo '<b>SK Nace divízia: </b>'.        $response->SkNaceDivision.'<br />';
    if($response instanceof ExtendedResult|| isset($response->ActualYear))
    {
    echo '<b>Príznak, či sa daná firma je živnostník: </b>';
        if($response->SelfEmployed) echo 'Áno <br />'; else echo 'Nie<br />';
    }
    echo '<b>SK Nace skupina: </b>'.        $response->SkNaceGroup.'<br />';
    echo '<b>Pozastavená(živnosť): </b>'.   (($response->SuspendedAsPerson)? "Ano": "Nie").'<br />';
    echo '<b>Zisk za aktuálny rok: </b>'.                       $response->ProfitActual.'<br />';
    echo '<b>Suma celkových výnosov za aktuálny rok: </b>'.     $response->RevenueActual.'<br />';
    if($response instanceof ExtendedResult|| isset($response->ActualYear))
    {
        echo '<b>Kód počtu zamestnancov: </b>'.                     $response->EmployeeCode.'<br />';
        echo '<b>Text počtu zamestnancov: </b>'.                    $response->EmployeeText.'<br />';
        echo '<b>Aktuálny rok: </b>'.                               $response->ActualYear.'<br />';
        echo '<b>Credit scoring: </b>'.                             $response->CreditScoreValue.'<br />';
        echo '<b>Credit scoring - text: </b>'.                      $response->CreditScoreState.'<br />';
        echo '<b>Zisk za predošlý rok: </b>'.                       $response->ProfitPrev.'<br />';
        echo '<b>Suma celkových výnosov za predošlý rok: </b>'.     $response->RevenuePrev.'<br />';
        echo '<b>Pomer cudzích zdrojov za aktuálny rok : </b>'.     $response->ForeignResources.'<br />';
        echo '<b>Hrubá marža za aktuálny rok: </b>'.                $response->GrossMargin.'<br />';
        echo '<b>ROA výnosov za aktuálny rok: </b>'.                $response->ROA.'<br />';
        echo '<b>Posledný dátum zmeny v Konkurzoch a Reštrukturalizáciach: </b>'. (($response->WarningKaR) ?  echoDate($response->WarningKaR, $json) : '').'<br />';
        echo '<b>Posledný dátum zmeny v Likvidáciach: </b>'.        (($response->WarningLiquidation) ?  echoDate($response->WarningLiquidation, $json) : '').'<br />';
    }
    echo '<b>Url: </b>'.            $response->Url.'<br />';
    echo '<b>Príznak, či sa daná firma nachádza v zoznamoch dlžníkov, konkurzov alebo likvidácií: </b>';
    if($response->Warning) echo 'Áno (<a href="'.$response->WarningUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';
    if($response instanceof ExtendedResult|| isset($response->ActualYear))
    {
        echo '<b>Príznak, či sa daná firma má evidované konkurzy: </b>';
        if($response->HasKaR) echo 'Áno (<a href="'.$response->KaRUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';
        echo '<b>Príznak, či sa daná firma má evidované dlhy: </b>';
        if($response->HasDebt) echo 'Áno (<a href="'.$response->DebtUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';
        echo '<b>Príznak, či sa daná firma má evidované likvidácie: </b>';
        if($response->HasDisposal) echo 'Áno (<a href="'.$response->DisposalUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';
    }
    echo '<b>Príznak, či má platobné príkazy: </b> ';
    if($response->PaymentOrderWarning) echo 'Áno (<a href="'.$response->PaymentOrderUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';
    echo '<b>Príznak, či nastala pre danú firmu zmena v ORSR počas posledných 3 mesiacov: </b> ';
    if($response->OrChange) echo 'Áno (<a href="'.$response->OrChangeUrl.'">viac info</a>)<br />'; else echo 'Nie<br />';
    if($response instanceof DetailResult || isset($response->Profit))
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
    echo '<b>Link na súdne rozhodnutia: </b>'.                  $response->JudgementFinstatLink.'<br />';
    if (!empty($response->JudgementIndicators))
    {
        echo '<b>Indikátory Súdnych rozhodnutí: </b><br />';
        if(!empty($response->JudgementIndicators)) {
            echo "<br /><table>";
            echo
            "<tr><th>Názov" .
            "</th><th>Hodnota" .
            "</th></tr>";
            foreach($response->JudgementIndicators as $in) {
                echo "<tr><td>" . $in->Name;
                echo "</td><td>" . (($in->Value) ? "true" : "false" );
                echo "</td></tr>";
            }
            echo "</table><br />";
        }
    }
    if ($response instanceof ExtendedResult)
    {
        if(!empty($response->JudgementCounts)) {
            echo '<b>Počty Súdnych rozhodnutí: </b><br />';
            if(!empty($response->JudgementCounts)) {
                echo "<br /><table>";
                echo
                "<tr><th>Názov" .
                "</th><th>Hodnota" .
                "</th></tr>";
                foreach($response->JudgementCounts as $in) {
                    echo "<tr><td>" . $in->Name;
                    echo "</td><td>" . $in->Value;
                    echo "</td></tr>";
                }
                echo "</table><br />";
            }
        }
        echo '<b>Dátum posledného súdneho rozhodnutia: </b>'.           (($response->JudgementLastPublishedDate) ? echoDate($response->JudgementLastPublishedDate, $json) : '') .'<br />';
        if (!empty($response->Ratios))
        {
            echo '<b>Ukazovatele: </b><br />';
            if(!empty($response->Ratios)) {
                echo "<br /><table>";
                echo
                "<tr><th>Názov" .
                "</th><th>Hodnota" .
                "</th></tr>";
                foreach($response->Ratios as $ratio) {
                    echo "<tr><td>" . $ratio->Name. "</td><td>";
                    foreach($ratio->Values as $value) {
                        echo $value->Year . ":". (($value->Value !== null) ? $value->Value : "") . ", ";
                    }
                    echo "</td></tr>";
                }
                echo "</table><br />";
            }
        }
    }

    if($response instanceof ExtendedResult || isset($response->ActualYear))
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
                echo "<tr><td>" . $debt->Source. "</td><td>" . $debt->Value.  "</td><td>" . (($debt->ValidFrom) ? echoDate($debt->ValidFrom, $json) : '') .'</td></tr>';
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
                echo "<tr><td>" . (($paymentOrder->PublishDate) ? echoDate($paymentOrder->PublishDate, $json) : '') . "</td><td>" . $paymentOrder->Value.  "</td></tr>";
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
                        (($subject->ValidFrom) ? echoDate($subject->ValidFrom, $json) : '').
                        "</td><td>" .
                        (($subject->SuspendedFrom) ? echoDate($subject->SuspendedFrom, $json) : '').
                        "</td></tr>";
            }
            echo "</table><br />";
        }
        if ($response->SelfEmployed && !empty($response->StructuredName)) {
            echo echoStructuredName($response->StructuredName). "<br />";
        }
        echo '<br />';
    }
    if($response instanceof UltimateResult || isset($response->ORSection))
    {
        if (!empty($response->EmployeesNumber)) {
            echo '<b>Presny pocet zamestnancov: </b>'.            $response->EmployeesNumber.'<br />';
        }
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
                            $functions .= " (" . echoDate($function->From, $json) . ")";
                        }
                        $functions .="<br />";
                    }
                }
                echo
                    "<tr><td>" . $person->FullName . "<br /> ". echoStructuredName($person->StructuredName) .
                    "</td><td>" . $person->Street ." " . $person->StreetNumber. ", " . $person->ZipCode . ", " . $person->City .  ", " . $person->District .  ", " . $person->Region .  ", " . $person->Country .
                    "</td><td>" . (($person->DetectedFrom) ? echoDate($person->DetectedFrom, $json) : '') .
                    "</td><td>" . (($person->DetectedTo) ? echoDate($person->DetectedTo, $json) : '') .
                    "</td><td>" . $functions .
                    "</td><td>" . $person->DepositAmount . "/" . $person->PaybackRange .
                    "</td></tr>";
            }
            echo "</table><br />";
        }
        if (!empty($response->RpvsPersons)) {
            echo '<b>RPVS osoby: </b><br />';
            echo "<br /><table>";
            echo
            "<tr><th>Meno" .
            "</th><th>Datum nar." .
            "</th><th>Ico" .
            "</th><th>Adresa" .
            "</th><th>Detekovane od" .
            "</th><th>Detekovane do" .
            "</th><th>Funckcia" .
            "</th></tr>";
            foreach ($response->RpvsPersons as $person) {
                $functions = "";
                if (!empty($person->Functions)) {
                    foreach ($person->Functions as $function) {
                        $functions .= $function->Type . " - ";
                        $functions .= $function->Description;
                        if ($function->From) {
                            $functions .= " (" . echoDate($function->From, $json) . ")";
                        }
                        $functions .="<br />";
                    }
                }
                echo
                "<tr><td>" . $person->FullName .
                "</td><td>" . (($person->BirthDate) ? echoDate($person->BirthDate, $json) : '') .
                "</td><td>" . $person->Ico .
                "</td><td>" . $person->Street ." " . $person->StreetNumber. ", " . $person->ZipCode . ", " . $person->City .  ", " . $person->District .  ", " . $person->Region .  ", " . $person->Country .
                "</td><td>" . (($person->DetectedFrom) ? echoDate($person->DetectedFrom, $json) : '') .
                "</td><td>" . (($person->DetectedTo) ? echoDate($person->DetectedTo, $json) : '') .
                "</td><td>" . $functions .
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
                    "</td><td>" . (($address->ValidFrom) ? echoDate($address->ValidFrom, $json) : '') .
                    "</td><td>" . (($address->ValidTo) ? echoDate($address->ValidTo, $json) : '') .
                    "</td></tr>";
            }
            echo "</table><br />";
        }

        if(!empty($response->Bankrupt) || !empty($response->Restructuring) || !empty($response->Liquidation) || !empty($response->OtherProceeding)) {
            echo '<b>Konkurz / Reštruktualizácia / Likvidácia/Iné Konanie: </b><br />';
            echo "<br /><table><tr>";
            echo "<tr>";
            echo
                "</th><th>" .
                "</th><th>Dátum vstupu" .
                "</th><th>" .
                "</th><th>Dátum začiatku" .
                "</th><th>Dátum výstupu" .
                "</th><th>" .
                "</th><th> Správca" .
                "</th><th> Stav" .
                "</th><th> Zdroj" .
                "</th></tr>";
            if(!empty($response->Bankrupt)) {
                echo "<tr><th>Konkurz</th></td><td>".
                    (($response->Bankrupt->EnterDate) ? echoDate($response->Bankrupt->EnterDate, $json) : '') ."</td><td>".
                    $response->Bankrupt->EnterReason."</td><td>".
                    (($response->Bankrupt->StartDate) ? echoDate($response->Bankrupt->StartDate, $json) : '') ."</td><td>".
                    (($response->Bankrupt->ExitDate) ? echoDate($response->Bankrupt->ExitDate, $json) : '') ."</td><td>".
                    $response->Bankrupt->ExitReason."</td><td>".
                    (($response->Bankrupt->Officer) ? $response->Bankrupt->Officer->FullName . "<br />" . echoStructuredName($response->Bankrupt->Officer->StructuredName) : ''). "</td><td>".
                     $response->Bankrupt->Source."</td><td>".
                     $response->Bankrupt->Status."</td><td>".
                    "</td></tr>";
                if (!empty($response->Bankrupt->Deadlines)) {
                    echo "<tr><th colspan='9'>Lehoty</th></tr>";
                    foreach ($response->Bankrupt->Deadlines as $deadline) {
                        echo "<tr><td colspan='9'>".
                        (($deadline->Date) ? echoDate($deadline->Date, $json) : '') . ' '.
                        $deadline->Type.
                        "</td></tr>";
                    }
                }
            }
            if(!empty($response->Restructuring)) {
                echo "<tr><th>Reštrukturalizácia</th></td><td>".
                    (($response->Restructuring->EnterDate) ? echoDate($response->Restructuring->EnterDate, $json) : '') ."</td><td>".
                    $response->Restructuring->EnterReason."</td><td>".
                    (($response->Restructuring->StartDate) ? echoDate($response->Restructuring->StartDate, $json) : '') ."</td><td>".
                    (($response->Restructuring->ExitDate) ? echoDate($response->Restructuring->ExitDate, $json) : '') ."</td><td>".
                    $response->Restructuring->ExitReason."</td><td>".
                    (($response->Restructuring->Officer) ? $response->Restructuring->Officer->FullName . "<br />" . echoStructuredName($response->Restructuring->Officer->StructuredName) : '')."</td><td>".
                    $response->Restructuring->Source."</td><td>".
                    $response->Restructuring->Status."</td><td>".
                    "</td></tr>";
                if (!empty($response->Restructuring->Deadlines)) {
                    echo "<tr><th colspan='9'>Lehoty</th></tr>";
                    foreach ($response->Restructuring->Deadlines as $deadline) {
                        echo "<tr><td colspan='9'>".
                        (($deadline->Date) ? echoDate($deadline->Date, $json) : '') . ' '.
                        $deadline->Type.
                        "</td></tr>";
                    }
                }
            }
            if(!empty($response->Liquidation)) {
                echo "<tr><th>Likvidácia</th></td><td>".
                    (($response->Liquidation->EnterDate) ? echoDate($response->Liquidation->EnterDate, $json) : '') ."</td><td>".
                    $response->Liquidation->EnterReason."</td><td>".
                    "</td><td>".
                    (($response->Liquidation->ExitDate) ? echoDate($response->Liquidation->ExitDate, $json) : '') ."</td><td>".
                    "</td><td>".
                    (($response->Liquidation->Officer) ? $response->Liquidation->Officer->FullName . "<br />" . echoStructuredName($response->Liquidation->Officer->StructuredName): '')."</td><td>".
                    $response->Bankrupt->Source."</td><td>".
                    "</td><td>".
                    "</td></tr>";
                if (!empty($response->Liquidation->Deadlines)) {
                    echo "<tr><th colspan='9'>Lehoty</th></tr>";
                    foreach ($response->Liquidation->Deadlines as $deadline) {
                        echo "<tr><td colspan='9'>".
                        (($deadline->Date) ? echoDate($deadline->Date, $json) : '') . ' '.
                        $deadline->Type.
                        "</td></tr>";
                    }
                }
            }
			if(!empty($response->OtherProceeding)) {
                echo "<tr><th>Iné Konanie</th></td><td>".
                    (($response->OtherProceeding->EnterDate) ? echoDate($response->OtherProceeding->EnterDate, $json) : '') ."</td><td>".
                    $response->OtherProceeding->EnterReason."</td><td>".
                    (($response->OtherProceeding->StartDate) ? echoDate($response->OtherProceeding->StartDate, $json) : '') ."</td><td>".
                    (($response->OtherProceeding->ExitDate) ? echoDate($response->OtherProceeding->ExitDate, $json) : '') ."</td><td>".
                    $response->OtherProceeding->ExitReason."</td><td>".
                    (($response->OtherProceeding->Officer) ? $response->OtherProceeding->Officer->FullName . "<br />" . echoStructuredName($response->OtherProceeding->Officer->StructuredName) : '')."</td><td>".
                    $response->OtherProceeding->Source."</td><td>".
                    $response->OtherProceeding->Status."</td><td>".
                    "</td></tr>";
                if (!empty($response->OtherProceeding->Deadlines)) {
                    echo "<tr><th colspan='9'>Lehoty</th></tr>";
                    foreach ($response->OtherProceeding->Deadlines as $deadline) {
                        echo "<tr><td colspan='9'>".
                        (($deadline->Date) ? echoDate($deadline->Date, $json) : '') . ' '.
                        $deadline->Type.
                        "</td></tr>";
                    }
                }
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

function echoLimits($limits) {
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
$api = new FinstatApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

// priklad dopytu na detail firmy, ktora ma ICO 35757442
$ico = (isset($_GET['ico']) && !empty($_GET['ico'])) ? $_GET['ico'] : '35757442';
?>
header('Content-Type: text/html; charset=utf-8');
<h1>Detail test:</h1>
// priklad vypisu ziskanych udajov z Finstatu
<?php
try
{
    // funkcia $api->RequestDetail(string) vracia naplneny objekt typu DetailResult s udajmi o dopytovanej firme
    if (!empty($ico)) {
        $response = $api->Request($ico, "detail", $json);
    }
    echoBase($response, $json);
    echoLimits($api->GetAPILimits());
}
catch (Exception $e)
{
    echoException($e);
    echoLimits($api->GetAPILimits());
}

echo '<hr />';
?>
<h1>Extended test:</h1>
<?php
try
{
    // funkcia $api->RequestExtended(string) vracia naplneny objekt typu ExtendedResult s udajmi o dopytovanej firme
    if (!empty($ico)) {
        $response2 = $api->Request($ico, 'extended', $json);
    }
}
catch (Exception $e)
{
    echoException($e);
}
echoBase($response2, $json);
echo '<hr />';
?>
<h1>Ultimate test:</h1>
<?php
try
{
    // funkcia $api->RequestExtended(string) vracia naplneny objekt typu ExtendedResult s udajmi o dopytovanej firme
    if (!empty($ico)) {
        $response3 = $api->Request($ico, 'ultimate', $json);
    }
}
catch (Exception $e)
{
    echoException($e);
}
echoBase($response3, $json);
echo '<hr />';
?>
<h1>AutoComplete test "volkswagen":</h1>
<?php
try
{
    $response4 = $api->RequestAutoComplete('volkswagen', $json);
}
catch (Exception $e)
{
    echoException($e);
}
echoAutoComplete($response4, $json);
