<?php
/**
 * Example: FinStat CZ API Usage
 * 
 * This example demonstrates how to:
 * - Request basic company information (Czech Republic)
 * - Get detailed company data with CZ NACE codes
 * - Retrieve premium data with financial data and turnover category
 * - Access elite tier data with contacts, vehicle fleet, ISIR proceedings and financial indicators
 * - Access ultimate tier data with persons, trade licences, credit scores and filed insolvency claims
 * - Choose the language of response texts (cs|sk) per request
 * - Use autocomplete for company search
 * - Handle API exceptions properly
 * 
 * @package FinStatCZ
 * @author FinStat s.r.o.
 * @link https://cz.finstat.sk/
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Import required classes
use FinStatCZ\FinstatApi;
use FinStat\Client\Exceptions\NotFoundException;
use FinStat\Client\Exceptions\LimitReachedException;
use FinStat\Client\Exceptions\AuthenticationException;
use FinStat\Client\Exceptions\AccessDisabledException;
use FinStat\Client\Exceptions\BadRequestException;
use FinStat\Client\Exceptions\GdprRestrictionException;
use FinStat\Client\Exceptions\InsufficientAccessException;
use FinStat\Client\Exceptions\InvalidHashException;
use FinStat\Client\Exceptions\LicenseExpiredException;
use FinStat\Client\Exceptions\UnauthorizedException;
use FinStat\Client\Exceptions\FinstatException;

// ============================================
// CONFIGURATION - Update these values
// ============================================
$apiUrl = 'https://cz.finstat.sk/api/';
$apiKey = 'YOUR_API_KEY';
$privateKey = 'YOUR_PRIVATE_KEY';
$stationId = 'YOUR_STATION_ID';
$stationName = 'YOUR_STATION_NAME';
$timeout = 10;

// Use JSON format instead of XML?
$useJson = false;

// Language of response texts, passed with each request: 'cs', 'sk' or null for the server default (Czech on the Czech API)
$lang = null;

// Test values
$testIco = '00177041';
$testSearchTerm = 'skoda';

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
        echo "<tr><th style=\"text-align: left;\">Response:</th><td><pre style=\"background-color: #fff; padding: 10px;\">" . htmlspecialchars($e->getData()) . "</pre></td></tr>";
    }
    
    if ($e instanceof LimitReachedException) {
        echo "<tr><th style=\"text-align: left;\">Daily Limit:</th><td>{$e->getDailyCurrent()} / {$e->getDailyMax()}</td></tr>";
        echo "<tr><th style=\"text-align: left;\">Monthly Limit:</th><td>{$e->getMonthlyCurrent()} / {$e->getMonthlyMax()}</td></tr>";
    }
    
    echo "</table>";
    echo "</div>";
}

/**
 * Display company information based on tier (basic, detail, premium, elite)
 * @param object $response Company data object
 * @param bool $json Whether response was in JSON format
 */
function echoBase($response, $json = false)
{
    echo "<pre style=\"background-color: #f5f5f5; padding: 15px; border-left: 3px solid #2196F3;\">";
    
    // Basic information available in all tiers
    echo '<b>IČO:</b> ' . htmlspecialchars($response->Ico) . '<br />';
    echo '<b>Název:</b> ' . htmlspecialchars($response->Name) . '<br />';
    echo '<b>Ulice:</b> ' . htmlspecialchars($response->Street) . '<br />';
    echo '<b>Číslo ulice:</b> ' . htmlspecialchars($response->StreetNumber) . '<br />';
    echo '<b>PSČ:</b> ' . htmlspecialchars($response->ZipCode) . '<br />';
    echo '<b>Město:</b> ' . htmlspecialchars($response->City) . '<br />';
    echo '<b>Okres:</b> ' . htmlspecialchars($response->District) . '<br />';
    echo '<b>Kraj:</b> ' . htmlspecialchars($response->Region) . '<br />';
    echo '<b>Stát:</b> ' . htmlspecialchars($response->Country) . '<br />';
    echo '<b>Založená:</b> ' . (($response->Created) ? echoDate($response->Created, $json) : '') . '<br />';
    echo '<b>Zrušená:</b> ' . (($response->Cancelled) ? echoDate($response->Cancelled, $json) : '') . '<br />';
    echo '<b>Pozastavena živnost:</b> ' . ($response->SuspendedAsPerson ? "ano" : "nie") . '<br />';
    
    // Basic tier includes VAT information
    if ($response instanceof \FinStatCZ\ViewModel\Detail\BasicResult) {
        echo '<b>DIČ:</b> ' . htmlspecialchars($response->VatNumber) . '<br />';
        echo '<b>Plátce DPH:</b> ' . htmlspecialchars($response->TaxPayer) . '<br />';
    }

    // Detail tier includes activity and structure information
    if ($response instanceof \FinStatCZ\ViewModel\Detail\DetailResult) {
        echo '<b>Odvětví:</b> ' . htmlspecialchars($response->Activity) . '<br />';
        echo '<b>CZ NACE Kód:</b> ' . htmlspecialchars($response->CzNaceCode) . '<br />';
        echo '<b>CZ NACE Text:</b> ' . htmlspecialchars($response->CzNaceText) . '<br />';
        echo '<b>CZ NACE Divize:</b> ' . htmlspecialchars($response->CzNaceDivision) . '<br />';
        echo '<b>CZ NACE Skupina:</b> ' . htmlspecialchars($response->CzNaceGroup) . '<br />';
        echo '<b>Právní forma:</b> ' . htmlspecialchars($response->LegalForm) . '<br />';
        echo '<b>Druh vlastnictví:</b> ' . htmlspecialchars($response->OwnershipType) . '<br />';
        echo '<b>Počet zaměstnanců:</b> ' . htmlspecialchars($response->EmployeeCount) . '<br />';
    
        // Premium CZ tier includes financial data
        if ($response instanceof \FinStatCZ\ViewModel\Detail\PremiumCZResult) {
            echo '<b>DIČ:</b> ' . htmlspecialchars($response->VatNumber) . '<br />';
            echo '<b>Plátce DPH:</b> ' . htmlspecialchars($response->TaxPayer) . '<br />';
            echo '<b>Právní forma (kód):</b> ' . htmlspecialchars($response->LegalFormCode) . '<br />';
            echo '<b>Druh vlastnictví (kód):</b> ' . htmlspecialchars($response->OwnershipCode) . '<br />';
            echo '<b>Počet zaměstnanců (kód):</b> ' . htmlspecialchars($response->EmployeeCode ?? '') . '<br />';
            echo '<b>Nespolehlivost:</b> ' . ($response->UnReliability ? "ano" : "nie") . '<br />';
            echo '<b>Registrace:</b> ' . htmlspecialchars($response->RegisterNumberText) . '<br />';
            echo '<b>Evidující úřad:</b> ' . htmlspecialchars($response->TradeLicensingOffice) . '<br />';
            // EliteCZ/UltimateCZ have the yearly values in FinancialIndicators instead
            if (!($response instanceof \FinStatCZ\ViewModel\Detail\EliteCZResult)) {
                echo '<b>Aktuální rok:</b> ' . htmlspecialchars($response->ActualYear ?? '') . '<br />';
                echo '<b>Tržby (aktuální):</b> ' . htmlspecialchars($response->SalesActual ?? '') . '<br />';
                echo '<b>Zisk (aktuální):</b> ' . htmlspecialchars($response->ProfitActual ?? '') . '<br />';
            }
            echo '<b>Tržby:</b> ' . htmlspecialchars($response->Sales ?? '') . '<br />';
            echo '<b>Zisk:</b> ' . htmlspecialchars($response->Profit ?? '') . '<br />';
            echo '<b>Pozastavena živnost do:</b> ' . echoDate($response->SuspendedAsPersonUntil, $json) . '<br />';
            if (!empty($response->TurnoverCategory)) {
                echo '<b>Kategorie obratu:</b> ' . htmlspecialchars($response->TurnoverCategory->Text)
                    . ' (kód ' . htmlspecialchars($response->TurnoverCategory->Code) . ', rok ' . htmlspecialchars($response->TurnoverCategory->Year) . ')<br />';
            }

            // Display bank accounts if available
            if (!empty($response->BankAccounts)) {
                echo '<b>Bankové účty:</b><br />';
                echo "<table style=\"margin-left: 20px;\">";
                echo "<tr><th>Číslo účtu</th><th>Datum zveřejnění</th></tr>";
                foreach ($response->BankAccounts as $bac) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($bac->AccountNumber) . "</td>";
                    echo "<td>" . echoDate($bac->PublishedAt, $json) . "</td>";
                    echo "</tr>";
                }
                echo "</table><br />";
            }
        }
        
        // Elite CZ tier includes contacts, offices, vehicle fleet, ISIR proceedings and financial indicators
        if ($response instanceof \FinStatCZ\ViewModel\Detail\EliteCZResult) {
            echoEliteCZ($response, $json);
        }

        // Ultimate CZ tier includes persons, trade licences, credit scores and filed insolvency claims
        if ($response instanceof \FinStatCZ\ViewModel\Detail\UltimateCZResult) {
            echoUltimateCZ($response, $json);
        }
    }

    echo '<b>URL:</b> <a href="' . htmlspecialchars($response->Url) . '" target="_blank">' . htmlspecialchars($response->Url) . '</a><br />';
    
    // Show insolvency warning if present
    if ($response instanceof \FinStatCZ\ViewModel\Detail\DetailResult) {
        echo '<b>Insolvence:</b> ';
        if ($response->Warning) {
            echo 'Ano (<a href="' . htmlspecialchars($response->WarningUrl) . '" target="_blank">více info</a>)<br />';
        } else {
            echo 'Ne<br />';
        }
    }

    echo "</pre>";
}

/**
 * Format ISIR parties (debtors, creditors, officers) as one line
 * @param array $parties IsirPartyResult objects
 * @param bool $json Whether response was in JSON format
 * @return string HTML
 */
function echoIsirParties($parties, $json = false)
{
    $items = array();
    foreach ((array)$parties as $p) {
        if (empty($p)) {
            continue;
        }
        $item = htmlspecialchars($p->Name);
        if (!empty($p->Ico)) {
            $item .= ' (IČO ' . htmlspecialchars($p->Ico) . ')';
        } elseif (!empty($p->BirthDate)) {
            $item .= ' (nar. ' . echoDate($p->BirthDate, $json) . ')';
        }
        $items[] = $item;
    }
    return implode(', ', $items);
}

/**
 * Display year => value series (financial indicators, credit scores)
 * @param array $series Objects with Name and Values (Year, Value[, State])
 * @return void
 */
function echoYearSeries($series)
{
    $years = array();
    foreach ($series as $s) {
        foreach ($s->Values as $v) {
            $years[$v->Year] = true;
        }
    }
    krsort($years);
    echo "<table style=\"margin-left: 20px;\">";
    echo "<tr><th>Název</th>";
    foreach (array_keys($years) as $year) {
        echo "<th>" . htmlspecialchars($year) . "</th>";
    }
    echo "</tr>";
    foreach ($series as $s) {
        echo "<tr><td>" . htmlspecialchars($s->Name) . "</td>";
        foreach (array_keys($years) as $year) {
            $cell = '';
            foreach ($s->Values as $v) {
                if ($v->Year == $year) {
                    $cell = ($v->Value === null) ? '---' : htmlspecialchars($v->Value);
                    if (!empty($v->State)) {
                        $cell .= ' (' . htmlspecialchars($v->State) . ')';
                    }
                }
            }
            echo "<td>{$cell}</td>";
        }
        echo "</tr>";
    }
    echo "</table><br />";
}

/**
 * Display Elite CZ tier data
 * Fields marked "UltimateCZ" in the view models stay empty in EliteCZ responses.
 * @param \FinStatCZ\ViewModel\Detail\EliteCZResult $response
 * @param bool $json Whether response was in JSON format
 */
function echoEliteCZ($response, $json = false)
{
    echo '<b>Skupina DPH:</b> ' . (!empty($response->VatGroupMember) ? "ano" : "ne") . '<br />';

    if (!empty($response->Contacts)) {
        echo '<b>Kontakty:</b><br />';
        echo "<table style=\"margin-left: 20px;\">";
        echo "<tr><th>Typ</th><th>Kontakt</th></tr>";
        foreach ($response->Contacts as $c) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($c->Type) . "</td>";
            echo "<td>" . htmlspecialchars($c->Contact) . "</td>";
            echo "</tr>";
        }
        echo "</table><br />";
    }

    if (!empty($response->OtherCzNaces)) {
        echo '<b>Další CZ NACE:</b><br />';
        echo "<table style=\"margin-left: 20px;\">";
        echo "<tr><th>Kód</th><th>Název</th></tr>";
        foreach ($response->OtherCzNaces as $n) {
            echo "<tr><td>" . htmlspecialchars($n->Code) . "</td><td>" . htmlspecialchars($n->Text) . "</td></tr>";
        }
        echo "</table><br />";
    }

    if (!empty($response->Offices)) {
        echo '<b>Provozovny:</b> ' . htmlspecialchars($response->Offices->Count) . '<br />';
        // UltimateCZ only
        if (!empty($response->Offices->Items)) {
            echo "<table style=\"margin-left: 20px;\">";
            echo "<tr><th>Název</th><th>Adresa</th><th>Předměty</th></tr>";
            foreach ($response->Offices->Items as $o) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($o->Name) . "</td>";
                echo "<td>" . htmlspecialchars(trim("{$o->Street} {$o->StreetNumber}, {$o->ZipCode} {$o->City}", ' ,')) . "</td>";
                echo "<td>" . htmlspecialchars(implode(', ', (array)$o->Subjects)) . "</td>";
                echo "</tr>";
            }
            echo "</table><br />";
        }
    }

    if (!empty($response->VehicleFleet)) {
        $f = $response->VehicleFleet;
        echo '<b>Vozový park (' . htmlspecialchars($f->Year) . '):</b> aktivní vozidla ' . htmlspecialchars($f->ActiveCount)
            . ' (<a href="' . htmlspecialchars($f->Url) . '" target="_blank">detail</a>)<br />';
        // UltimateCZ only
        if ($f->VehicleCount !== null) {
            echo '<b>Počet vozidel:</b> ' . htmlspecialchars($f->VehicleCount) . '<br />';
            $breakdowns = array(
                'Vlastnictví' => $f->OwnershipTypes,
                'Druh vozidla' => $f->VehicleTypes,
                'Rok výroby' => $f->ProductionYears,
            );
            foreach ($breakdowns as $title => $items) {
                $parts = array();
                foreach ($items as $i) {
                    $parts[] = htmlspecialchars($i->Type) . ': ' . htmlspecialchars($i->Count);
                }
                echo '<b>' . $title . ':</b> ' . implode(', ', $parts) . '<br />';
            }
            $brands = array();
            foreach ($f->TopBrands as $b) {
                $brands[] = htmlspecialchars($b->Name) . ': ' . htmlspecialchars($b->Count);
            }
            echo '<b>Top značky:</b> ' . implode(', ', $brands) . '<br />';
        }
    }

    if (!empty($response->IsirProceedings)) {
        echo '<b>Insolvenční řízení (ISIR):</b><br />';
        echo "<table style=\"margin-left: 20px;\">";
        echo "<tr><th>Spisová značka</th><th>Soud</th><th>Stav</th><th>První / poslední událost</th><th>Dlužníci</th></tr>";
        foreach ($response->IsirProceedings as $p) {
            echo "<tr>";
            echo "<td><a href=\"" . htmlspecialchars($p->Url) . "\" target=\"_blank\">" . htmlspecialchars($p->FileReference) . "</a></td>";
            echo "<td>" . htmlspecialchars($p->CourtName ?? '') . " (" . htmlspecialchars($p->Court ?? '') . ")</td>";
            echo "<td>" . htmlspecialchars($p->StatusText ?? '') . " (" . htmlspecialchars($p->Status) . ")</td>";
            echo "<td>" . echoDate($p->FirstEvent, $json) . " / " . echoDate($p->LastEvent, $json) . "</td>";
            echo "<td>" . echoIsirParties($p->Debtors, $json) . "</td>";
            echo "</tr>";
            // UltimateCZ only
            if (isset($p->Type) || isset($p->CreditorsCount)) {
                echo "<tr><td colspan=\"5\" style=\"padding-left: 30px;\">";
                echo '<b>Typ:</b> ' . htmlspecialchars($p->TypeText ?? '') . ' (' . htmlspecialchars($p->Type ?? '') . '), <b>zahájeno:</b> ' . echoDate($p->StartDate, $json) . '<br />';
                echo '<b>Správci:</b> ' . echoIsirParties($p->Officers, $json) . '<br />';
                echo '<b>Věřitelé (' . htmlspecialchars($p->CreditorsCount) . '):</b> ' . echoIsirParties($p->Creditors, $json) . '<br />';
                echo '<b>Výše přihlášených pohledávek:</b> ' . htmlspecialchars($p->AmountOfClaims) . '<br />';
                foreach ($p->ReceivableApplications as $a) {
                    echo '&nbsp;&nbsp;- ' . echoDate($a->PublishDate, $json) . ' ' . echoIsirParties(array($a->Creditor), $json)
                        . (!empty($a->Representative) ? ' zast. ' . echoIsirParties(array($a->Representative), $json) : '')
                        . ': ' . htmlspecialchars($a->ClaimsCount) . ' pohl., ' . htmlspecialchars($a->TotalAmount) . '<br />';
                }
                echo "</td></tr>";
            }
        }
        echo "</table><br />";
    }

    if (!empty($response->FinancialIndicators)) {
        echo '<b>Finanční ukazatele:</b><br />';
        echoYearSeries($response->FinancialIndicators);
    }
}

/**
 * Display Ultimate CZ tier data (on top of Elite CZ data)
 * @param \FinStatCZ\ViewModel\Detail\UltimateCZResult $response
 * @param bool $json Whether response was in JSON format
 */
function echoUltimateCZ($response, $json = false)
{
    echo '<b>Jednání statutárního orgánu:</b> ' . htmlspecialchars($response->StatutoryAction) . '<br />';
    echo '<b>Jednání prokury:</b> ' . htmlspecialchars($response->ProcurationAction) . '<br />';

    if (!empty($response->ORCRPersons)) {
        echo '<b>Osoby (OR ČR):</b><br />';
        echo "<table style=\"margin-left: 20px;\">";
        echo "<tr><th>Jméno</th><th>IČO / narozen</th><th>Funkce</th><th>Vklad</th><th>Podíl %</th></tr>";
        foreach ($response->ORCRPersons as $p) {
            $functions = array();
            foreach ($p->Functions as $f) {
                $functions[] = htmlspecialchars($f->Description ?: $f->TypeText) . ' (' . htmlspecialchars($f->Type) . ') od ' . echoDate($f->From, $json);
            }
            echo "<tr>";
            echo "<td>" . htmlspecialchars($p->FullName) . "</td>";
            echo "<td>" . (!empty($p->Ico) ? htmlspecialchars($p->Ico) : echoDate($p->BirthDate, $json)) . "</td>";
            echo "<td>" . implode('<br />', $functions) . "</td>";
            echo "<td>" . htmlspecialchars($p->DepositAmount) . "</td>";
            echo "<td>" . htmlspecialchars($p->PartnersSharePercentage) . "</td>";
            echo "</tr>";
        }
        echo "</table><br />";
    }

    if (!empty($response->RZPPersons)) {
        echo '<b>Osoby (RŽP):</b><br />';
        echo "<table style=\"margin-left: 20px;\">";
        echo "<tr><th>Jméno</th><th>Narozen</th><th>Funkce</th><th>Od</th><th>Živnosti</th></tr>";
        foreach ($response->RZPPersons as $p) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($p->FullName) . "</td>";
            echo "<td>" . echoDate($p->BirthDate, $json) . "</td>";
            echo "<td>" . htmlspecialchars($p->Function) . "</td>";
            echo "<td>" . echoDate($p->FunctionFrom, $json) . "</td>";
            echo "<td>" . htmlspecialchars(implode(', ', (array)$p->Subjects)) . "</td>";
            echo "</tr>";
        }
        echo "</table><br />";
    }

    if (!empty($response->Subjects)) {
        echo '<b>Předměty podnikání / živnosti:</b><br />';
        echo "<table style=\"margin-left: 20px;\">";
        echo "<tr><th>Název</th><th>Platnost od</th><th>Přerušeno</th></tr>";
        foreach ($response->Subjects as $s) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($s->Title) . "</td>";
            echo "<td>" . echoDate($s->ValidFrom, $json) . "</td>";
            echo "<td>" . ($s->SuspendedFrom ? echoDate($s->SuspendedFrom, $json) . ' - ' . echoDate($s->SuspendedTo, $json) : '') . "</td>";
            echo "</tr>";
        }
        echo "</table><br />";
    }

    if (!empty($response->CreditScores)) {
        echo '<b>Kreditní skóre:</b><br />';
        echoYearSeries($response->CreditScores);
    }

    if (!empty($response->FiledInsolvencyClaims)) {
        $c = $response->FiledInsolvencyClaims;
        echo '<b>Přihlášené pohledávky v insolvencích (jako věřitel):</b> řízení ' . htmlspecialchars($c->ProceedingsCount)
            . ', celkem ' . ($c->AmountOfClaims === null ? '---' : htmlspecialchars($c->AmountOfClaims)) . '<br />';
        if (!empty($c->Applications)) {
            echo "<table style=\"margin-left: 20px;\">";
            echo "<tr><th>Spisová značka</th><th>Zveřejněno</th><th>Dlužníci</th><th>Zástupce</th><th>Počet</th><th>Suma</th></tr>";
            foreach ($c->Applications as $a) {
                echo "<tr>";
                echo "<td><a href=\"" . htmlspecialchars($a->Url) . "\" target=\"_blank\">" . htmlspecialchars($a->FileReference) . "</a></td>";
                echo "<td>" . echoDate($a->PublishDate, $json) . "</td>";
                echo "<td>" . echoIsirParties($a->Debtors, $json) . "</td>";
                echo "<td>" . echoIsirParties(array($a->Representative), $json) . "</td>";
                echo "<td>" . htmlspecialchars($a->ClaimsCount) . "</td>";
                echo "<td>" . htmlspecialchars($a->TotalAmount) . "</td>";
                echo "</tr>";
            }
            echo "</table><br />";
        }
    }
}

/**
 * Display autocomplete search results
 * @param object $response Autocomplete result object
 */
function echoAutoComplete($response)
{
    echo "<pre style=\"background-color: #f5f5f5; padding: 15px; border-left: 3px solid #2196F3;\">";
    echo '<b>Výsledky:</b><br />';
    
    if (!empty($response->Results)) {
        echo "<table>";
        echo "<tr><th>IČO</th><th>Název</th><th>Město</th><th>Zrušena</th></tr>";
        foreach ($response->Results as $company) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($company->Ico) . "</td>";
            echo "<td>" . htmlspecialchars($company->Name) . "</td>";
            echo "<td>" . htmlspecialchars($company->City) . "</td>";
            echo "<td>" . (($company->Cancelled) ? "ano" : "ne") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p><i>Nenalezeny žádné výsledky.</i></p>";
    }
    
    echo '<br /><b>Návrhy:</b> ';
    if (!empty($response->Suggestions)) {
        echo htmlspecialchars(implode(', ', $response->Suggestions));
    } else {
        echo "<i>Žádné návrhy.</i>";
    }
    
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
        echo '<tr><th></th><th>Aktuální</th><th>MAX</th></tr>';
        echo '<tr>' .
            '<th>Denní</th>' .
            '<th>' . ((isset($limits['daily']) && isset($limits['daily']['current'])) ? $limits['daily']['current'] : "---") . '</th>' .
            '<th>' . ((isset($limits['daily']) && isset($limits['daily']['max'])) ? $limits['daily']['max'] : "---") . '</th>' .
            '</tr>';
        echo '<tr>' .
            '<th>Měsíční</th>' .
            '<th>' . ((isset($limits['monthly']) && isset($limits['monthly']['current'])) ? $limits['monthly']['current'] : "---") . '</th>' .
            '<th>' . ((isset($limits['monthly']) && isset($limits['monthly']['max'])) ? $limits['monthly']['max'] : "---") . '</th>' .
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
th { background-color: #2196F3; color: white; }
h1 { color: #333; border-bottom: 2px solid #2196F3; padding-bottom: 10px; }
h2 { color: #555; }
pre { background-color: #f5f5f5; padding: 15px; border-left: 3px solid #2196F3; }
.success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; margin: 10px 0; }
.info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 10px; margin: 10px 0; }
hr { margin: 30px 0; border: none; border-top: 2px solid #2196F3; }
</style>";

echo "<h1>🇨🇿 FinStat CZ API Example</h1>";
echo "<div class='info'><b>ℹ Info:</b> Access company data from Czech business registers (ARES, Justice.cz) with financial and insolvency information.</div>";

try {
    // Initialize the API client
    $api = new FinstatApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);
    echo "<div class='success'>✓ API client initialized successfully</div>";
} catch (AuthenticationException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
    echo "<h2 style='margin-top: 0;'>❌ Authentication Failed</h2>";
    echo "<p>Please configure your API credentials in the CONFIGURATION section.</p>";
    echo "<p><b>Error:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Contact <a href='mailto:info@finstat.sk'>info@finstat.sk</a> for API access.</p>";
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
// EXAMPLE 1: Basic Tier Request
// ============================================
echo "<h1>📄 Example 1: Basic Company Information</h1>";
echo "<p><b>Endpoint:</b> Request('$testIco', 'basic')</p>";
echo "<p>Basic tier includes: company identification, address, registration dates, VAT status</p>";

try {
    $response = $api->Request($testIco, 'basic', $useJson, $lang);
    echoBase($response, $useJson);
    echoLimitsSafe($api);
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>⚠ Company Not Found</h3>";
    echo "<p>IČO: " . htmlspecialchars($testIco) . "</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (LimitReachedException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>⛔ API Limit Reached</h3>";
    echo "<p><b>Daily:</b> {$e->getDailyCurrent()} / {$e->getDailyMax()}</p>";
    echo "<p><b>Monthly:</b> {$e->getMonthlyCurrent()} / {$e->getMonthlyMax()}</p>";
    echo "</div>";
} catch (GdprRestrictionException $e) {
    // HTTP 451 — the requested company is GDPR-anonymized and the current
    // licence does not permit anonymized records.
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>🔒 GDPR Restricted</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
}

echo '<hr />';

// ============================================
// EXAMPLE 2: Detail Tier Request
// ============================================
echo "<h1>📋 Example 2: Detailed Company Information</h1>";
echo "<p><b>Endpoint:</b> Request('$testIco', 'detail')</p>";
echo "<p>Detail tier adds: CZ NACE codes, legal form, ownership type, employee count, insolvency warnings</p>";

try {
    $response = $api->Request($testIco, 'detail', $useJson, $lang);
    echoBase($response, $useJson);
    echoLimitsSafe($api);
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>⚠ Company Not Found</h3>";
    echo "<p>IČO: " . htmlspecialchars($testIco) . "</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
}

echo '<hr />';

// ============================================
// EXAMPLE 3: Premium CZ Tier Request
// ============================================
echo "<h1>💎 Example 3: Premium CZ Company Information</h1>";
echo "<p><b>Endpoint:</b> Request('$testIco', 'premiumcz')</p>";
echo "<p>Premium CZ tier adds: financial data (sales, profit), unreliability status, bank accounts, registration details</p>";

try {
    $response = $api->Request($testIco, 'premiumcz', $useJson, $lang);
    echoBase($response, $useJson);
    echoLimitsSafe($api);
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>⚠ Company Not Found</h3>";
    echo "<p>IČO: " . htmlspecialchars($testIco) . "</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
}

echo '<hr />';

// ============================================
// EXAMPLE 4: Elite CZ Tier Request
// ============================================
echo "<h1>👑 Example 4: Elite CZ Company Information</h1>";
echo "<p><b>Endpoint:</b> Request('$testIco', 'elitecz')</p>";
echo "<p>Elite CZ tier adds: contacts, other CZ NACE activities, office count, vehicle fleet, ISIR insolvency proceedings, financial indicators (2 years)</p>";

try {
    $response = $api->RequestEliteCZ($testIco, $useJson, $lang);
    echoBase($response, $useJson);
    echoLimitsSafe($api);
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>⚠ Company Not Found</h3>";
    echo "<p>IČO: " . htmlspecialchars($testIco) . "</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
}

echo '<hr />';

// ============================================
// EXAMPLE 5: Ultimate CZ Tier Request
// ============================================
echo "<h1>🏆 Example 5: Ultimate CZ Company Information</h1>";
echo "<p><b>Endpoint:</b> RequestUltimateCZ('$testIco')</p>";
echo "<p>Ultimate CZ tier adds: OR ČR / RŽP persons, statutory and procuration action, business subjects / trade licences, office list, "
    . "vehicle fleet breakdowns, ISIR creditors and claim applications, financial indicators and credit scores (4 years), "
    . "insolvency claims filed by the company as creditor, web and social network contacts</p>";

try {
    $response = $api->RequestUltimateCZ($testIco, $useJson, $lang);
    echoBase($response, $useJson);
    echoLimitsSafe($api);
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>⚠ Company Not Found</h3>";
    echo "<p>IČO: " . htmlspecialchars($testIco) . "</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
}

echo '<hr />';

// ============================================
// EXAMPLE 6: AutoComplete Search
// ============================================
echo "<h1>🔍 Example 6: Company Search (AutoComplete)</h1>";
echo "<p><b>Endpoint:</b> RequestAutoComplete('$testSearchTerm')</p>";
echo "<p>Search for companies by name, get suggestions and company list with basic info</p>";

try {
    $response = $api->RequestAutoComplete($testSearchTerm, $useJson);
    echoAutoComplete($response);
    echoLimitsSafe($api);
} catch (BadRequestException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>❌ Bad Request</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
}

echo '<hr />';
echo "<div class='info'><b>✓ Complete:</b> All Czech Republic API examples executed successfully.</div>";
