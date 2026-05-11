<?php
/**
 * Example: FinStat API Complete Usage Guide
 * 
 * This comprehensive example demonstrates how to:
 * - Initialize the FinStat API client with proper configuration
 * - Request different levels of company data (Basic, Detail, Extended, Ultimate)
 * - Use AutoComplete search functionality
 * - Handle different types of API exceptions (Not Found, Rate Limits, Authentication)
 * - Monitor API usage limits
 * - Process and display company information
 * - Work with both XML and JSON response formats
 * 
 * @package FinStat
 * @author FinStat s.r.o.
 * @link https://www.finstat.sk/
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Import required classes
use FinStat\Api\FinstatApi;
use FinStat\Client\Exceptions\NotFoundException;
use FinStat\Client\Exceptions\LimitReachedException;
use FinStat\Client\Exceptions\AuthenticationException;
use FinStat\Client\Exceptions\AccessDisabledException;
use FinStat\Client\Exceptions\BadRequestException;
use FinStat\Client\Exceptions\GdprRestrictionException;
use FinStat\Client\Exceptions\InsufficientAccessException;
use FinStat\Client\Exceptions\InvalidHashException;
use FinStat\Client\Exceptions\LicenseExpiredException;
use FinStat\Client\Exceptions\ParseException;
use FinStat\Client\Exceptions\UnauthorizedException;
use FinStat\Client\Exceptions\FinstatException;
use FinStat\ViewModel\Detail\BaseResult;
use FinStat\ViewModel\Detail\BasicResult;
use FinStat\ViewModel\Detail\DetailResult;
use FinStat\ViewModel\Detail\ExtendedResult;
use FinStat\ViewModel\Detail\UltimateResult;
use FinStat\ViewModel\Detail\CommonResult;

// ============================================
// CONFIGURATION - Update these values
// ============================================
$apiUrl = 'https://www.finstat.sk/api/';
$apiKey = 'F0C44EBE9E4D4CDE9FB51C7B811495F9';
$privateKey = 'C5278277CA944B5CB428E559356937E4';
$stationId = 'YOUR_STATION_ID';
$stationName = 'YOUR_STATION_NAME';
$timeout = 10;

// Use JSON format instead of XML? (false = XML, true = JSON)
$useJson = false;

// Company ICO to query (can be overridden via ?ico= parameter)
$ico = isset($_GET['ico']) && !empty($_GET['ico']) ? $_GET['ico'] : '47004428';

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Format date for display
 * @param mixed $date DateTime object or date string
 * @param bool $json Whether the date came from JSON response
 * @return string Formatted date (d.m.Y)
 */
function echoDate($date, $json = false)
{
    if ($date && !empty($date)) {
        if ($json) {
            $date = new DateTime($date);
        }
        return $date->format('d.m.Y');
    }
    return '';
}

/**
 * Display structured name parts (Prefix, Name, Suffix, After)
 * @param object $data Name structure object
 * @return string HTML formatted name parts
 */
function echoStructuredName($data)
{
    $result ="";
    if ($data && !empty($data)) {
        $result = '<b>Štrukturovane meno: </b><br />' .
        ((!empty($data->Prefix)) ? "Prefix: " .   implode(" ", $data->Prefix) . "<br />" : "") .
        ((!empty($data->Name)) ? "Name: " .     implode(" ", $data->Name) . "<br />" : "").
        ((!empty($data->Suffix)) ? "Suffix: " .   implode(" ", $data->Suffix) . "<br />" : "").
        ((!empty($data->After)) ? "After: " .    implode(" ", $data->After) . "<br />" : "");
    }
    return $result;
}


/**
 * Display company base information
 * Handles all response types: BaseResult, BasicResult, DetailResult, ExtendedResult, UltimateResult
 * @param object $response Company data object
 * @param bool $json Whether response was in JSON format
 */
function echoBase($response, $json = false)
{
    echo "<pre>";
    echo '<b>IČO: </b>'.                    $response->Ico.'<br />';
    if ($response instanceof BaseResult) {
        echo '<b>Reg. Číslo: </b>'.         $response->RegisterNumberText.'<br />';
    }
    echo '<b>DIČ: </b>'.                    $response->Dic.'<br />';
    echo '<b>IčDPH: </b>'.                  $response->IcDPH.'<br />';
    if ($response instanceof BasicResult) {
        echo '<b>Paragraf: </b>'.              $response->Paragraph.'<br />';
    }
    echo '<b>Založená: </b>'.               (($response->Created) ? echoDate($response->Created, $json) : '').'<br />';
    echo '<b>Zrušená: </b>'.                (($response->Cancelled) ? echoDate($response->Cancelled, $json) : '') .'<br />';
    echo '<b>Pozastavená(živnosť): </b>'.  (($response->SuspendedAsPerson) ?  "Ano" : "Nie").'<br />';
    if ($response instanceof ExtendedResult) {
        echo '<b>Základné imanie: </b>'.                             $response->BasicCapital.'<br />';
        if ($response instanceof UltimateResult || isset($response->ORSection)) {
            echo '<b>OR Odiel: </b>'.                                   $response->ORSection.'<br />';
            echo '<b>OR Vložka: </b>'.                                  $response->ORInsertNo.'<br />';
            echo '<b>Rozsah splatenia: </b>'.                            $response->PaybackRange.'<br />';
            if (!empty($response->RegistrationCourt)) {
                echo '<b>Registrovane na: </b>'.                        $response->RegistrationCourt->Name . ', ' . $response->RegistrationCourt->Street . ' ' . $response->RegistrationCourt->StreetNumber.  ", " . $response->RegistrationCourt->ZipCode . ", " . $response->RegistrationCourt->City .  ", " . $response->RegistrationCourt->District .  ", " . $response->RegistrationCourt->Region .  ", " . $response->RegistrationCourt->Country .'<br />';
            }
        }
    }
    if ($response instanceof BaseResult) {
        echo '<b>Detail IČDPH: IČDPH: </b>'.                         (!empty($response->IcDphAdditional) ? $response->IcDphAdditional->IcDph : '') .'<br />';
        echo '<b>Detail IČDPH: Paragraf: </b>'.                      (!empty($response->IcDphAdditional) ? $response->IcDphAdditional->Paragraph : '') .'<br />';
        echo '<b>Detail IČDPH: Dátum detekovania v zozname subjektov, u ktorých nastali dôvody na zrušenie: </b>'.
        (!empty($response->IcDphAdditional) && ($response->IcDphAdditional->CancelListDetectedDate) ? echoDate($response->IcDphAdditional->CancelListDetectedDate, $json) : '').'<br />';
        echo '<b>Detail IČDPH: Dátum detekovania v zozname vymazaných subjektov: </b>'.
        (!empty($response->IcDphAdditional) && ($response->IcDphAdditional->RemoveListDetectedDate) ? echoDate($response->IcDphAdditional->RemoveListDetectedDate, $json) : '').'<br />';
        echo '<b>Rpvs: </b>'.                   $response->RpvsInsert. ' '. $response->RpvsUrl .'<br />';
    }
    echo '<b>Názov: </b>'.                  $response->Name.'<br />';
    echo '<b>Ulica: </b>'.                  $response->Street.'<br />';
    echo '<b>Číslo ulice: </b>'.            $response->StreetNumber.'<br />';
    echo '<b>PSČ: </b>'.                    $response->ZipCode.'<br />';
    echo '<b>Mesto: </b>'.                  $response->City.'<br />';
    echo '<b>Okres: </b>'.                  $response->District.'<br />';
    echo '<b>Kraj: </b>'.                   $response->Region.'<br />';
    echo '<b>Štát: </b>'.                   $response->Country.'<br />';
    echo '<b>Anonymizované: </b>'.                  ($response->Anonymized ? "Áno" : "nie").'<br />';
    if ($response instanceof BaseResult) {
        echo '<b>Kategoria Tržieb: </b>'.       $response->SalesCategory.'<br />';
        if ($response instanceof ExtendedResult|| isset($response->ActualYear)) {
            echo '<b>Tel. čisla: </b>'.                                 implode(', ', $response->Phones).'<br />';
            echo '<b>Emaily: </b>'.                                     implode(', ', $response->Emails).'<br />';
            if (!empty($response->ContactSources)) {
                echo '<b>Zdroje:</b> <br />';
                foreach ($response->ContactSources as $source) {
                    echo $source->Contact . ' -'. (!empty($source->Sources) ? implode(', ', $source->Sources) : '') .'<br />';
                }
            }
        }
        echo '<b>Odvetvie: </b>'.               $response->Activity.'<br />';
        if ($response instanceof UltimateResult || isset($response->ORSection)) {
            echo '<b>Zrušená podľa OR: </b>'.                (($response->ORCancelled) ? echoDate($response->ORCancelled, $json) : '') .'<br />';
        }
        echo '<b>Právna forma kód: </b>'.                           $response->LegalFormCode.'<br />';
        echo '<b>Právna forma popis: </b>'.                         $response->LegalFormText.'<br />';
        if ($response instanceof ExtendedResult|| isset($response->ActualYear)) {
            echo '<b>Druh vlastníctva kód: </b>'.                       $response->OwnershipTypeCode.'<br />';
            echo '<b>Druh vlastníctva popis: </b>'.                     $response->OwnershipTypeText.'<br />';
        }
        echo '<b>SK Nace kód: </b>'.            $response->SkNaceCode.'<br />';
        echo '<b>SK Nace popis: </b>'.          $response->SkNaceText.'<br />';
        echo '<b>SK Nace divízia: </b>'.        $response->SkNaceDivision.'<br />';
        if ($response instanceof ExtendedResult || isset($response->ActualYear)) {
            echo '<b>Príznak, či sa daná firma je živnostník: </b>';
            if ($response->SelfEmployed) {
                echo 'Áno <br />';
            } else {
                echo 'Nie<br />';
            }
        }
        echo '<b>SK Nace skupina: </b>'.        $response->SkNaceGroup.'<br />';
        echo '<b>Pozastavená(živnosť): </b>'.   (!empty($response->SuspendedAsPersonUntil) ? echoDate($response->SuspendedAsPersonUntil, $json) : "-") .'<br />';
        echo '<b>Zisk za aktuálny rok: </b>'.                       $response->ProfitActual.'<br />';
        echo '<b>Suma celkových výnosov za aktuálny rok: </b>'.     $response->RevenueActual.'<br />';
        if ($response instanceof ExtendedResult || isset($response->ActualYear)) {
            echo '<b>Kód počtu zamestnancov: </b>'.                     $response->EmployeeCode.'<br />';
            echo '<b>Text počtu zamestnancov: </b>'.                    $response->EmployeeText.'<br />';
            echo '<b>Aktuálny rok: </b>'.                               $response->ActualYear.'<br />';
            echo '<b>Credit scoring: </b>'.                             $response->CreditScoreValue.'<br />';
            echo '<b>Credit scoring - text: </b>'.                      $response->CreditScoreState.'<br />';
            echo '<b>Credit scoring Index05: </b>'.                     $response->CreditScoreValueIndex05.'<br />';
            echo '<b>Credit scoring Index05 - text: </b>'.              $response->CreditScoreStateIndex05.'<br />';
            echo '<b>Credit scoring FinStat Score: </b>'.               $response->CreditScoreValueFinStatScore.'<br />';
            echo '<b>Credit scoring FinStat Score - text: </b>'.        $response->CreditScoreStateFinStatScore.'<br />';
            echo '<b>Zisk za predošlý rok: </b>'.                       $response->ProfitPrev.'<br />';
            echo '<b>Suma celkových výnosov za predošlý rok: </b>'.     $response->RevenuePrev.'<br />';
            echo '<b>Pomer cudzích zdrojov za aktuálny rok : </b>'.     $response->ForeignResources.'<br />';
            echo '<b>Hrubá marža za aktuálny rok: </b>'.                $response->GrossMargin.'<br />';
            echo '<b>ROA výnosov za aktuálny rok: </b>'.                $response->ROA.'<br />';
            echo '<b>Posledný dátum zmeny v Konkurzoch a Reštrukturalizáciach: </b>'. (($response->WarningKaR) ? echoDate($response->WarningKaR, $json) : '').'<br />';
            echo '<b>Posledný dátum zmeny v Likvidáciach: </b>'.        (($response->WarningLiquidation) ? echoDate($response->WarningLiquidation, $json) : '').'<br />';
        }
    }
    echo '<b>Url: </b>'.            $response->Url.'<br />';
    if ($response instanceof CommonResult) {
        echo '<b>Príznak, či sa daná firma nachádza v zoznamoch dlžníkov, konkurzov alebo likvidácií: </b>';
        if ($response->Warning) {
            echo 'Áno (<a href="'.$response->WarningUrl.'">viac info</a>)<br />';
        } else {
            echo 'Nie<br />';
        }
        if ($response instanceof BaseResult) {
            echo '<b>Príznak, či sa daná firma má evidované konkurzy: </b>';
            if ($response->HasKaR) {
                echo 'Áno (<a href="'.$response->KaRUrl.'">viac info</a>)<br />';
            } else {
                echo 'Nie<br />';
            }
            echo '<b>Príznak, či sa daná firma má evidované dlhy: </b>';
            if ($response->HasDebt) {
                echo 'Áno (<a href="'.$response->DebtUrl.'">viac info</a>)<br />';
            } else {
                echo 'Nie<br />';
            }
            if ($response instanceof ExtendedResult|| isset($response->ActualYear)) {
                echo '<b>Príznak, či sa daná firma má evidované likvidácie: </b>';
                if ($response->HasDisposal) {
                    echo 'Áno (<a href="'.$response->DisposalUrl.'">viac info</a>)<br />';
                } else {
                    echo 'Nie<br />';
                }
            }
            echo '<b>Príznak, či má platobné príkazy: </b> ';
            if ($response->PaymentOrderWarning) {
                echo 'Áno (<a href="'.$response->PaymentOrderUrl.'">viac info</a>)<br />';
            } else {
                echo 'Nie<br />';
            }
            echo '<b>Príznak, či nastala pre danú firmu zmena v ORSR počas posledných 3 mesiacov: </b> ';
            if ($response->OrChange) {
                echo 'Áno (<a href="'.$response->OrChangeUrl.'">viac info</a>)<br />';
            } else {
                echo 'Nie<br />';
            }
        }
        if ($response instanceof DetailResult || $response instanceof ExtendedResult || $response instanceof UltimateResult || isset($response->Profit)) {
            echo '<b>Príznak nárastu/poklesu tržieb firmy medzi posledným a predposledným rokom v databáze: </b>';
            switch ($response->Revenue) {
                case 'Unknown': echo 'Neznámy';
                    break;
                case 'Up': echo 'Nárast (<a href="'.$response->Url.'">viac info</a>)';
                    break;
                case 'Down': echo 'Pokles (<a href="'.$response->Url.'">viac info</a>)';
                    break;
            }
            echo '<br />';
            echo '<b>Príznak nárastu/poklesu zisku firmy medzi posledným a predposledným rokom v databáze: </b>';
            switch ($response->Profit) {
                case 'Unknown': echo 'Neznámy';
                    break;
                case 'Up': echo 'Nárast (<a href="'.$response->Url.'">viac info</a>)';
                    break;
                case 'Down': echo 'Pokles (<a href="'.$response->Url.'">viac info</a>)';
                    break;
                case 'Loss': echo 'Firma bola posledný rok v strate (<a href="'.$response->Url.'">viac info</a>)';
                    break;
            }
            echo '<br />';
        }
        echo '<b>Link na súdne rozhodnutia: </b>'.                  $response->JudgementFinstatLink.'<br />';
        if (!empty($response->JudgementIndicators)) {
            echo '<b>Indikátory Súdnych rozhodnutí: </b><br />';
            if (!empty($response->JudgementIndicators)) {
                echo "<br /><table>";
                echo
                "<tr><th>Názov" .
                "</th><th>Hodnota" .
                "</th></tr>";
                foreach ($response->JudgementIndicators as $in) {
                    echo "<tr><td>" . $in->Name;
                    echo "</td><td>" . (($in->Value) ? "true" : "false");
                    echo "</td></tr>";
                }
                echo "</table><br />";
            }
        }
        if (!empty($response->BankAccounts)) {
            echo '<b>Bankové účty: </b><br />';
            if (!empty($response->BankAccounts)) {
                echo "<br /><table>";
                echo
                "<tr><th>Čislo účtu" .
                "</th><th>Dátum zverejnenia" .
                "</th></tr>";
                foreach ($response->BankAccounts as $bac) {
                    echo "<tr><td>" . $bac->AccountNumber;
                    echo "</td><td>" . echoDate($bac->PublishedAt, $json);
                    echo "</td></tr>";
                }
                echo "</table><br />";
            }
        }
        echo '<b>Index daňovej spoľahlvosti: </b>'. $response->TaxReliabilityIndex.'<br />';
        if ($response instanceof ExtendedResult) {
            if (!empty($response->JudgementCounts)) {
                echo '<b>Počty Súdnych rozhodnutí: </b><br />';
                if (!empty($response->JudgementCounts)) {
                    echo "<br /><table>";
                    echo
                    "<tr><th>Názov" .
                    "</th><th>Hodnota" .
                    "</th></tr>";
                    foreach ($response->JudgementCounts as $in) {
                        echo "<tr><td>" . $in->Name;
                        echo "</td><td>" . $in->Value;
                        echo "</td></tr>";
                    }
                    echo "</table><br />";
                }
            }
            echo '<b>Dátum posledného súdneho rozhodnutia: </b>'.           (($response->JudgementLastPublishedDate) ? echoDate($response->JudgementLastPublishedDate, $json) : '') .'<br />';
            if (!empty($response->Ratios)) {
                echo '<b>Ukazovatele: </b><br />';
                if (!empty($response->Ratios)) {
                    echo "<br /><table>";
                    echo
                    "<tr><th>Názov" .
                    "</th><th>Hodnota" .
                    "</th></tr>";
                    foreach ($response->Ratios as $ratio) {
                        echo "<tr><td>" . $ratio->Name. "</td><td>";
                        foreach ($ratio->Values as $value) {
                            echo $value->Year . ":". (($value->Value !== null) ? $value->Value : "") . ", ";
                        }
                        echo "</td></tr>";
                    }
                    echo "</table><br />";
                }
            }
        }

        if ($response instanceof ExtendedResult || isset($response->ActualYear)) {
            echo '<b>Dlhy: </b><br />';
            if (!empty($response->Debts)) {
                echo "<br /><table>";
                echo
                        "<tr><th>Zdroj" .
                        "</th><th>Hodnota" .
                        "</th><th>Platné od" .
                        "</th></tr>";
                foreach ($response->Debts as $debt) {
                    echo "<tr><td>" . $debt->Source. "</td><td>" . $debt->Value.  "</td><td>" . (($debt->ValidFrom) ? echoDate($debt->ValidFrom, $json) : '') .'</td></tr>';
                }
                echo "</table><br />";
            }
            echo '<b>Pohľadávky štátu: </b><br />';
            if (!empty($response->StateReceivables)) {
                echo "<br /><table>";
                echo
                        "<tr><th>Zdroj" .
                        "</th><th>Hodnota" .
                        "</th><th>Platné od" .
                        "</th></tr>";
                foreach ($response->StateReceivables as $debt) {
                    echo "<tr><td>" . $debt->Source. "</td><td>" . $debt->Value.  "</td><td>" . (($debt->ValidFrom) ? echoDate($debt->ValidFrom, $json) : '') .'</td></tr>';
                }
                echo "</table><br />";
            }
            echo '<b>Komerčné pohľadávky</b><br />';
            if (!empty($response->CommercialReceivables)) {
                echo "<br /><table>";
                echo
                        "<tr><th>Zdroj" .
                        "</th><th>Hodnota" .
                        "</th><th>Platné od" .
                        "</th></tr>";
                foreach ($response->CommercialReceivables as $debt) {
                    echo "<tr><td>" . $debt->Source. "</td><td>" . $debt->Value.  "</td><td>" . (($debt->ValidFrom) ? echoDate($debt->ValidFrom, $json) : '') .'</td></tr>';
                }
                echo "</table><br />";
            }

            echo '<b>Platobné rozkazy: </b><br />';
            if (!empty($response->PaymentOrders)) {
                echo "<br /><table>";
                echo
                        "<tr><th>Dátum uverejnenia" .
                        "</th><th>Hodnota" .
                        "</th></tr>";
                foreach ($response->PaymentOrders as $paymentOrder) {
                    echo "<tr><td>" . (($paymentOrder->PublishDate) ? echoDate($paymentOrder->PublishDate, $json) : '') . "</td><td>" . $paymentOrder->Value.  "</td></tr>";
                }
                echo "</table><br />";
            }
            if (!empty($response->Offices)) {
                echo '<b>Prevádzky: </b><br />';
                echo "<br /><table>";
                echo
                        "<tr><th>Addesa" .
                        "</th><th>Predmety podnikania" .
                        "</th><th>Typ".
                        "</th></tr>";
                foreach ($response->Offices as $office) {
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
                        "</th><th>Pozastavené do".
                        "</th></tr>";
                foreach ($response->Subjects as $subject) {
                    echo    "<tr><td>" .
                            $subject->Title .
                            "</td><td>" .
                            (($subject->ValidFrom) ? echoDate($subject->ValidFrom, $json) : '').
                            "</td><td>" .
                            (($subject->SuspendedFrom) ? echoDate($subject->SuspendedFrom, $json) : '').
                            "</td><td>" .
                            (($subject->SuspendedTo) ? echoDate($subject->SuspendedTo, $json) : '').
                            "</td></tr>";
                }
                echo "</table><br />";
            }
            if ($response->SelfEmployed && !empty($response->StructuredName)) {
                echo echoStructuredName($response->StructuredName). "<br />";
            }
            echo '<br />';
            if (!empty($response->DistraintsAuthorization)) {
                echo '<b>Poverenia Exekucii: </b>';
                echo "Počet: " . $response->DistraintsAuthorization->Count . " (Poslednné: " . echoDate($response->DistraintsAuthorization->LastPublishDate, $json) .")";
                echo '<br />';
            }
        }
        if ($response instanceof UltimateResult || isset($response->ORSection)) {
            if (!empty($response->EmployeesNumber)) {
                echo '<b>Presny pocet zamestnancov: </b>'.            $response->EmployeesNumber.'<br />';
            }
            if (!empty($response->Persons)) {
                echo '<b>Osoby: </b><br />';
                echo "<br /><table>";
                echo
                    "<tr><th>Meno" .
                    "</th><th>Datum nar." .
                    "</th><th>Adresa" .
                    "</th><th>Detekovane od" .
                    "</th><th>Detekovane do" .
                    "</th><th>Funckcia" .
                    "</th><th>Podiel / Vyska splatenia" .
                    "</th><th>Percento podielu" .
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
                        "<tr><td>" .  $person->FullName . "<br /> ". echoStructuredName($person->StructuredName) .
                        "</td><td>" . (($person->BirthDate) ? echoDate($person->BirthDate, $json) : '') .
                        "</td><td>" . $person->Street ." " . $person->StreetNumber. ", " . $person->ZipCode . ", " . $person->City .  ", " . $person->District .  ", " . $person->Region .  ", " . $person->Country .
                        "</td><td>" . (($person->DetectedFrom) ? echoDate($person->DetectedFrom, $json) : '') .
                        "</td><td>" . (($person->DetectedTo) ? echoDate($person->DetectedTo, $json) : '') .
                        "</td><td>" . $functions .
                        "</td><td>" . $person->DepositAmount . "/" . $person->PaybackRange .
                        "</td><td>" . $person->PartnersSharePercentage . "%" .
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
                    "<tr><td>" . $person->FullName . "<br /> ". echoStructuredName($person->StructuredName) .
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
            if (!empty($response->RPOPersons)) {
                echo '<b>RPO osoby: </b><br />';
                echo "<br /><table>";
                echo
                "<tr><th>Meno" .
                "</th><th>Datum nar." .
                "</th><th>Adresa" .
                "</th><th>Detekovane od" .
                "</th><th>Detekovane do" .
                "</th><th>Funckcia" .
                "</th></tr>";
                foreach ($response->RPOPersons as $person) {
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
                    "</td><td>" . (($person->BirthDate) ? echoDate($person->BirthDate, $json) : '') .
                    "</td><td>" . $person->Citizenship .
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

            if (!empty($response->Bankrupt) || !empty($response->Restructuring) || !empty($response->PreventiveRestructuring) || !empty($response->Liquidation) || !empty($response->OtherProceeding)) {
                echo '<b>Konkurz / Reštruktualizácia / Likvidácia/Iné Konanie: </b><br />';
                echo "<br /><table><tr>";
                echo "<tr>";
                echo
                    "</th><th>".
                    "</th><th>Spisovná značka".
                    "</th><th>Kód súdu".
                    "</th><th>Dátum vstupu" .
                    "</th><th>" .
                    "</th><th>Dátum začiatku" .
                    "</th><th>Dátum výstupu" .
                    "</th><th>" .
                    "</th><th> Správca" .
                    "</th><th> Stav" .
                    "</th><th> Zdroj" .
                    "</th></tr>";
                if (!empty($response->Bankrupt)) {
                    echo "<tr><th>Konkurz</th></td><td>".
                        htmlspecialchars($response->Bankrupt->FileReference) ."</td><td>".
                        htmlspecialchars($response->Bankrupt->CourtCode) ."</td><td>".
                        (($response->Bankrupt->EnterDate) ? echoDate($response->Bankrupt->EnterDate, $json) : '') ."</td><td>".
                        $response->Bankrupt->EnterReason."</td><td>".
                        (($response->Bankrupt->StartDate) ? echoDate($response->Bankrupt->StartDate, $json) : '') ."</td><td>".
                        (($response->Bankrupt->ExitDate) ? echoDate($response->Bankrupt->ExitDate, $json) : '') ."</td><td>".
                        $response->Bankrupt->ExitReason."</td><td>".
                        (($response->Bankrupt->Officers) ? count($response->Bankrupt->Officers) : ''). "</td><td>".
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
                if (!empty($response->Restructuring)) {
                    echo "<tr><th>Reštrukturalizácia</th></td><td>".
                        (($response->Restructuring->FileReference) ? $response->Restructuring->FileReference : '') ."</td><td>".
                        (($response->Restructuring->CourtCode) ? $response->Restructuring->CourtCode : '') ."</td><td>".
                        (($response->Restructuring->EnterDate) ? echoDate($response->Restructuring->EnterDate, $json) : '') ."</td><td>".
                        $response->Restructuring->EnterReason."</td><td>".
                        (($response->Restructuring->StartDate) ? echoDate($response->Restructuring->StartDate, $json) : '') ."</td><td>".
                        (($response->Restructuring->ExitDate) ? echoDate($response->Restructuring->ExitDate, $json) : '') ."</td><td>".
                        $response->Restructuring->ExitReason."</td><td>".
                        (($response->Restructuring->Officers) ? count($response->Restructuring->Officers) : '') ."</td><td>".
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
                if (!empty($response->PreventiveRestructuring)) {
                    echo "<tr><th>Preventívna Reštrukturalizácia</th></td><td>".
                        (($response->PreventiveRestructuring->FileReference) ? $response->PreventiveRestructuring->FileReference : '') ."</td><td>".
                        (($response->PreventiveRestructuring->CourtCode) ? $response->PreventiveRestructuring->CourtCode : '') ."</td><td>".
                        (($response->PreventiveRestructuring->FirstDate) ? echoDate($response->PreventiveRestructuring->FirstDate, $json) : '') ."</td><td>".
                        "</td><td>".
                        "</td><td>".
                        (($response->PreventiveRestructuring->LastDate) ? echoDate($response->PreventiveRestructuring->LastDate, $json) : '') ."</td><td>".
                        "</td><td>".
                        "</td><td>".
                        $response->PreventiveRestructuring->Source."</td><td>".
                        "</td><td>".
                        "</td></tr>";
                    if (!empty($response->PreventiveRestructuring->Deadlines)) {
                        echo "<tr><th colspan='9'>Lehoty</th></tr>";
                        foreach ($response->PreventiveRestructuring->Deadlines as $deadline) {
                            echo "<tr><td colspan='9'>".
                            (($deadline->Date) ? echoDate($deadline->Date, $json) : '') . ' '.
                            $deadline->Type.
                            "</td></tr>";
                        }
                    }
                }
                if (!empty($response->Liquidation)) {
                    echo "<tr><th>Likvidácia</th></td><td>".
                        "</td><td>".
                        "</td><td>".
                        (($response->Liquidation->EnterDate) ? echoDate($response->Liquidation->EnterDate, $json) : '') ."</td><td>".
                        $response->Liquidation->EnterReason."</td><td>".
                        "</td><td>".
                        (($response->Liquidation->ExitDate) ? echoDate($response->Liquidation->ExitDate, $json) : '') ."</td><td>".
                        "</td><td>".
                        (($response->Liquidation->Officers) ? count($response->Liquidation->Officers): '')."</td><td>".
                        $response->Liquidation->Source."</td><td>".
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
                if (!empty($response->OtherProceeding)) {
                    echo "<tr><th>Iné Konanie</th></td><td>".
                        htmlspecialchars($response->OtherProceeding->FileReference) ."</td><td>".
                        htmlspecialchars($response->OtherProceeding->CourtCode) ."</td><td>".
                        (($response->OtherProceeding->EnterDate) ? echoDate($response->OtherProceeding->EnterDate, $json) : '') ."</td><td>".
                        $response->OtherProceeding->EnterReason."</td><td>".
                        (($response->OtherProceeding->StartDate) ? echoDate($response->OtherProceeding->StartDate, $json) : '') ."</td><td>".
                        (($response->OtherProceeding->ExitDate) ? echoDate($response->OtherProceeding->ExitDate, $json) : '') ."</td><td>".
                        $response->OtherProceeding->ExitReason."</td><td>".
                        (($response->OtherProceeding->Officers) ? count($response->OtherProceeding->Officers) : '')."</td><td>".
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
            if (!empty($response->DistraintsAuthorizations)) {
                echo '<b>Poverenia Exekucii Detail: </b>';
                echo "<br /><table><tr>";
                echo
                    "</th><th>Ref.Číslo" .
                    "</th><th>Oprávnený" .
                    "</th><th>Typ" .
                    "</th><th>Exekútor" .
                    "</th><th>Dátum Zverejnenia" .
                    "</th><th>URL" .
                    "</th><th>Súd" .
                    "</th><th>Identif. čislo" .
                    "</th></tr>";
                foreach ($response->DistraintsAuthorizations as $distraintsAuthorization) {
                    echo
                        "<tr></td><td>" . $distraintsAuthorization->ReferenceNumber .
                        "</td><td>" . ((!empty($distraintsAuthorization->Authorized)) ? implode(", ", array_map(function ($i) {
                            return $i->Name . ((!empty($i->Ico)) ? "(" . $i->Ico  . ")" : "");
                        }, $distraintsAuthorization->Authorized)) : '') .
                        "</td><td>" . $distraintsAuthorization->TypeOfClaim .
                        "</td><td>" . $distraintsAuthorization->Plaintiff .
                        "</td><td>" . echoDate($distraintsAuthorization->PublishDate, $json) .
                        "</td><td><a href=\"" . $distraintsAuthorization->Url ."\" >Link</a>".
                        "</td><td>" . $distraintsAuthorization->Court .
                        "</td><td>" . $distraintsAuthorization->IdentifierNumber .
                        "</td></tr>";
                }
                echo "</table><br />";
                echo '<br />';
            }
        }
    }
    echo "</pre>";
}

/**
 * Display exception information with proper error handling
 * @param Exception $e Exception object
 */
function echoException($e)
{
    echo "<div style=\"background-color: #ffebee; border-left: 4px solid #f44336; padding: 15px; margin: 10px 0;\">";
    echo "<h2 style=\"color: #c62828; margin-top: 0;\">⚠ Error</h2>";
    echo "<table style=\"width: 100%;\">";
    echo "<tr><th style=\"text-align: left; width: 120px;\">Error Type:</th><td>" . get_class($e) . "</td></tr>";
    echo "<tr><th style=\"text-align: left;\">Code:</th><td>{$e->getCode()}</td></tr>";
    echo "<tr><th style=\"text-align: left;\">Message:</th><td>" . htmlspecialchars($e->getMessage()) . "</td></tr>";
    
    if (method_exists($e, 'getData')) {
        echo "<tr><th style=\"text-align: left;\">Response Body:</th><td><pre style=\"background-color: #fff; padding: 10px;\">" . htmlspecialchars($e->getData()) . "</pre></td></tr>";
    }
    
    if ($e instanceof LimitReachedException) {
        echo "<tr><th style=\"text-align: left;\">Daily Limit:</th><td>{$e->getDailyCurrent()} / {$e->getDailyMax()}</td></tr>";
        echo "<tr><th style=\"text-align: left;\">Monthly Limit:</th><td>{$e->getMonthlyCurrent()} / {$e->getMonthlyMax()}</td></tr>";
    }
    
    if ($e instanceof NotFoundException && method_exists($e, 'getRequestParameter')) {
        echo "<tr><th style=\"text-align: left;\">Requested ICO:</th><td>{$e->getRequestParameter()}</td></tr>";
    }
    
    echo "</table>";
    echo "</div>";
}

/**
 * Display autocomplete search results
 * @param object $response AutoComplete result object
 */
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

/**
 * Display API usage limits (safely checks if limits are available)
 * @param FinstatApi $api API client instance
 */
function echoLimitsSafe($api)
{
    try {
        $limits = $api->GetAPILimits();
        echoLimits($limits);
    } catch (Exception $e) {
        // Limits not available (usually after HTTP failure)
        // Silently skip - this is expected
    }
}

/**
 * Display API usage limits
 * @param array $limits Limits array with 'daily' and 'monthly' keys
 */
function echoLimits($limits)
{
    if (!empty($limits)) {
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

// ============================================
// API CLIENT INITIALIZATION
// ============================================

// Set HTML header
header('Content-Type: text/html; charset=utf-8');

// Add CSS styles
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #4CAF50; color: white; }
h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
h2 { color: #555; }
pre { background-color: #f5f5f5; padding: 15px; border-left: 3px solid #4CAF50; }
.success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; margin: 10px 0; }
.info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 10px; margin: 10px 0; }
hr { margin: 30px 0; border: none; border-top: 2px solid #4CAF50; }
</style>";

echo "<h1>🏢 FinStat API Example - Complete Usage Guide</h1>";
echo "<div class='info'><b>ℹ Info:</b> This example demonstrates all available API endpoints and response types.</div>";

try {
    // Initialize the API client
    $api = new FinstatApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);
    echo "<div class='success'>✓ API client initialized successfully</div>";
    
} catch (AuthenticationException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
    echo "<h2 style='margin-top: 0;'>❌ Authentication Failed</h2>";
    echo "<p>Please configure your API credentials in the CONFIGURATION section at the top of this file.</p>";
    echo "<p><b>Error:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Contact <a href='mailto:info@finstat.sk'>info@finstat.sk</a> to obtain your API keys.</p>";
    echo "</div>";
    exit(1);
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
    echo "<h2 style='margin-top: 0;'>❌ Fatal Error</h2>";
    echo "<p><b>Error:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    exit(1);
}

// ============================================
// EXAMPLE 1: Basic Request
// ============================================
echo "<h1>📋 Example 1: Basic Company Information</h1>";
echo "<p><b>Endpoint:</b> Request('$ico', 'basic')</p>";
echo "<p><b>Description:</b> Retrieves basic company details including registration, contact, and business activity information.</p>";

try {
    if (!empty($ico)) {
        $response = $api->Request($ico, "basic", $useJson);
        echoBase($response, $useJson);
        echoLimitsSafe($api);
    }
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>⚠ Company Not Found</h3>";
    echo "<p>No company found with ICO: <b>" . htmlspecialchars($e->getRequestParameter()) . "</b></p>";
    echo "<p>Please verify the ICO number is correct.</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (LimitReachedException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>⛔ API Limit Reached</h3>";
    echo "<p><b>Daily:</b> {$e->getDailyCurrent()} / {$e->getDailyMax()}</p>";
    echo "<p><b>Monthly:</b> {$e->getMonthlyCurrent()} / {$e->getMonthlyMax()}</p>";
    echo "<p>Please wait until your quota resets or contact FinStat to increase your limits.</p>";
    echo "</div>";
} catch (BadRequestException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>❌ Bad Request</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (GdprRestrictionException $e) {
    // HTTP 451 — the requested company is GDPR-anonymized and the current
    // licence does not permit anonymized records.
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>🔒 GDPR Restricted</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Contact <a href='mailto:info@finstat.sk'>info@finstat.sk</a> to extend your licence with anonymized-records access.</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>❌ Unexpected Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo '<hr />';

// ============================================
// EXAMPLE 2: Detail Request
// ============================================
echo "<h1>📊 Example 2: Detailed Company Information</h1>";
echo "<p><b>Endpoint:</b> Request('$ico', 'detail')</p>";
echo "<p><b>Description:</b> Returns comprehensive company data including financial indicators, profit/revenue trends.</p>";

try {
    if (!empty($ico)) {
        $response = $api->Request($ico, "detail", $useJson);
        echoBase($response, $useJson);
        echoLimitsSafe($api);
    }
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>⚠ Company Not Found</h3>";
    echo "<p>No company found with ICO: <b>" . htmlspecialchars($e->getRequestParameter()) . "</b></p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (LimitReachedException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>⛔ API Limit Reached</h3>";
    echo "<p><b>Daily:</b> {$e->getDailyCurrent()} / {$e->getDailyMax()}</p>";
    echo "<p><b>Monthly:</b> {$e->getMonthlyCurrent()} / {$e->getMonthlyMax()}</p>";
    echo "</div>";
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>❌ Unexpected Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo '<hr />';

// ============================================
// EXAMPLE 3: Extended Request
// ============================================
echo "<h1>💼 Example 3: Extended Company Information</h1>";
echo "<p><b>Endpoint:</b> Request('$ico', 'extended')</p>";
echo "<p><b>Description:</b> Returns extended data including contact information, officers, credit scores, and more.</p>";

try {
    if (!empty($ico)) {
        $response2 = $api->Request($ico, 'extended', $useJson);
        echoBase($response2, $useJson);
        echoLimitsSafe($api);
    }
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>⚠ Company Not Found</h3>";
    echo "<p>No company found with ICO: <b>" . htmlspecialchars($e->getRequestParameter()) . "</b></p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (LimitReachedException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>⛔ API Limit Reached</h3>";
    echo "<p><b>Daily:</b> {$e->getDailyCurrent()} / {$e->getDailyMax()}</p>";
    echo "<p><b>Monthly:</b> {$e->getMonthlyCurrent()} / {$e->getMonthlyMax()}</p>";
    echo "</div>";
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>❌ Unexpected Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo '<hr />';

// ============================================
// EXAMPLE 4: Ultimate Request
// ============================================
echo "<h1>🏆 Example 4: Ultimate Company Information</h1>";
echo "<p><b>Endpoint:</b> Request('$ico', 'ultimate')</p>";
echo "<p><b>Description:</b> Returns the complete dataset including persons, statutory bodies, bankruptcies, and more.</p>";

try {
    if (!empty($ico)) {
        $response3 = $api->Request($ico, 'ultimate', $useJson);
        echoBase($response3, $useJson);
        echoLimitsSafe($api);
    }
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>⚠ Company Not Found</h3>";
    echo "<p>No company found with ICO: <b>" . htmlspecialchars($e->getRequestParameter()) . "</b></p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (LimitReachedException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>⛔ API Limit Reached</h3>";
    echo "<p><b>Daily:</b> {$e->getDailyCurrent()} / {$e->getDailyMax()}</p>";
    echo "<p><b>Monthly:</b> {$e->getMonthlyCurrent()} / {$e->getMonthlyMax()}</p>";
    echo "</div>";
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>❌ Unexpected Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo '<hr />';

// ============================================
// EXAMPLE 5: AutoComplete Search
// ============================================
echo "<h1>🔍 Example 5: AutoComplete Search</h1>";
echo "<p><b>Endpoint:</b> RequestAutoComplete('volkswagen')</p>";
echo "<p><b>Description:</b> Search for companies by name and get suggestions with company details.</p>";

try {
    $response4 = $api->RequestAutoComplete('volkswagen', $useJson);
    echoAutoComplete($response4, $useJson);
    echoLimitsSafe($api);
} catch (BadRequestException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>❌ Bad Request</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (LimitReachedException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>⛔ API Limit Reached</h3>";
    echo "<p><b>Daily:</b> {$e->getDailyCurrent()} / {$e->getDailyMax()}</p>";
    echo "<p><b>Monthly:</b> {$e->getMonthlyCurrent()} / {$e->getMonthlyMax()}</p>";
    echo "</div>";
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='margin-top: 0;'>❌ Unexpected Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo '<hr />';
echo "<div class='info'><b>✓ Complete:</b> All API examples executed successfully. Total requests processed for ICO: $ico</div>";
