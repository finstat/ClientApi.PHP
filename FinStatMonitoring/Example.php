<?php
/**
 * Example: FinStat Monitoring API Usage
 * 
 * This example demonstrates how to:
 * - Add companies and dates to monitoring watchlist
 * - Retrieve list of monitored entities
 * - Get monitoring reports with detected changes
 * - Remove companies/dates from monitoring
 * - Handle API exceptions properly
 * 
 * @package FinStat
 * @author FinStat s.r.o.
 * @link https://www.finstat.sk/
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Import required classes
use FinStat\Api\FinstatMonitoringApi;
use FinStat\Client\Exceptions\NotFoundException;
use FinStat\Client\Exceptions\LimitReachedException;
use FinStat\Client\Exceptions\AuthenticationException;
use FinStat\Client\Exceptions\BadRequestException;
use FinStat\Client\Exceptions\FinstatException;

// ============================================
// CONFIGURATION - Update these values
// ============================================
$apiUrl = 'https://www.finstat.sk/api/';
$apiKey = 'YOUR_API_KEY';
$privateKey = 'YOUR_PRIVATE_KEY';
$stationId = 'YOUR_STATION_ID';
$stationName = 'YOUR_STATION_NAME';
$timeout = 10;

// Use JSON format instead of XML?
$useJson = false;

// Test values
$testIco = '35757442';
$testDate = '1.1.1991';

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Format date with time for display
 * @param mixed $date DateTime object or date string
 * @param bool $json Whether the date came from JSON response
 * @return string Formatted date (d.m.Y H:i:s)
 */
function echoDate($date, $json = false)
{
    if ($date && !empty($date)) {
        if ($json) {
            $date = new DateTime($date);
        }
        return $date->format('d.m.Y H:i:s');
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
 * Display monitoring report changes with ALL available properties
 * @param array $response Array of monitoring report results  
 * @param bool $json Whether response was in JSON format
 */
function echoMonitoringReport($response, $json)
{
    echo "<div style=\"margin: 20px 0;\">";
    echo '<h3>📊 Monitoring Report</h3>';
    
    if (!empty($response)) {
        $icodate = "";
        if ($response[0] instanceof \FinStat\ViewModel\Monitoring\MonitoringDateReportResult) {
            $icodate = "Dátum";
        } else {
            $icodate = "IČO";
        }
        
        foreach ($response as $report) {
            echo "<div style=\"border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin: 10px 0; background-color: #f9f9f9;\">";
            
            // Basic Information
            echo "<h4 style=\"margin-top: 0; color: #1976d2;\">📄 " . htmlspecialchars($report->Name) . "</h4>";
            
            echo "<table style=\"width: 100%; border-collapse: collapse;\">";
            
            // Identifier
            $icodate = "";
            if ($report instanceof \FinStat\ViewModel\Monitoring\MonitoringDateReportResult) {
                $icodate = $report->Date;
                echo "<tr><td style=\"padding: 5px; font-weight: bold; width: 200px;\">📅 Dátum:</td><td style=\"padding: 5px;\">" . htmlspecialchars($icodate) . "</td></tr>";
            } else {
                $icodate = $report->Ico;
                echo "<tr><td style=\"padding: 5px; font-weight: bold; width: 200px;\">🏢 IČO:</td><td style=\"padding: 5px;\">" . htmlspecialchars($icodate) . "</td></tr>";
            }
            
            echo "<tr><td style=\"padding: 5px; font-weight: bold;\">🆔 Identifikátor:</td><td style=\"padding: 5px;\">" . htmlspecialchars($report->Ident) . "</td></tr>";
            echo "<tr><td style=\"padding: 5px; font-weight: bold;\">📋 Typ:</td><td style=\"padding: 5px;\">" . htmlspecialchars($report->Type) . "</td></tr>";
            echo "<tr><td style=\"padding: 5px; font-weight: bold;\">📝 Popis:</td><td style=\"padding: 5px;\">" . htmlspecialchars($report->Description) . "</td></tr>";
            echo "<tr><td style=\"padding: 5px; font-weight: bold;\">📅 Dátum zverejnenia:</td><td style=\"padding: 5px;\">" . (($report->PublishDate) ? echoDate($report->PublishDate, $json) : '-') . "</td></tr>";
            echo "<tr><td style=\"padding: 5px; font-weight: bold;\">🔗 URL:</td><td style=\"padding: 5px;\"><a href=\"" . htmlspecialchars($report->Url) . "\" target=\"_blank\">Zobraziť detail</a></td></tr>";
            
            // Status Information (if available)
            if (isset($report->Status) && !empty($report->Status)) {
                echo "<tr><td colspan=\"2\" style=\"padding: 10px 5px 5px 5px; font-weight: bold; color: #1976d2;\">📊 Status informácie</td></tr>";
                echo "<tr><td style=\"padding: 5px; font-weight: bold;\">⚡ Status:</td><td style=\"padding: 5px;\">" . htmlspecialchars($report->Status) . "</td></tr>";
            }
            
            if (isset($report->Character) && !empty($report->Character)) {
                echo "<tr><td style=\"padding: 5px; font-weight: bold;\">🏷️ Charakter:</td><td style=\"padding: 5px;\">" . htmlspecialchars($report->Character) . "</td></tr>";
            }
            
            if (isset($report->EndReason) && !empty($report->EndReason)) {
                echo "<tr><td style=\"padding: 5px; font-weight: bold;\">🛑 Dôvod ukončenia:</td><td style=\"padding: 5px;\">" . htmlspecialchars($report->EndReason) . "</td></tr>";
            }
            
            if (isset($report->EndStatus) && !empty($report->EndStatus)) {
                echo "<tr><td style=\"padding: 5px; font-weight: bold;\">✅ Stav ukončenia:</td><td style=\"padding: 5px;\">" . htmlspecialchars($report->EndStatus) . "</td></tr>";
            }
            
            // Reference Numbers (if available)
            if ((isset($report->ReferenceFileNumber) && !empty($report->ReferenceFileNumber)) || 
                (isset($report->FileIdentifierNumber) && !empty($report->FileIdentifierNumber))) {
                echo "<tr><td colspan=\"2\" style=\"padding: 10px 5px 5px 5px; font-weight: bold; color: #1976d2;\">📑 Referenčné čísla</td></tr>";
                
                if (isset($report->ReferenceFileNumber) && !empty($report->ReferenceFileNumber)) {
                    if (is_array($report->ReferenceFileNumber)) {
                        echo "<tr><td style=\"padding: 5px; font-weight: bold;\">📋 Referenčné spisové číslo:</td><td style=\"padding: 5px;\">" . implode(', ', array_map('htmlspecialchars', $report->ReferenceFileNumber)) . "</td></tr>";
                    } else {
                        echo "<tr><td style=\"padding: 5px; font-weight: bold;\">📋 Referenčné spisové číslo:</td><td style=\"padding: 5px;\">" . htmlspecialchars($report->ReferenceFileNumber) . "</td></tr>";
                    }
                }
                
                if (isset($report->FileIdentifierNumber) && !empty($report->FileIdentifierNumber)) {
                    if (is_array($report->FileIdentifierNumber)) {
                        echo "<tr><td style=\"padding: 5px; font-weight: bold;\">🔢 Identifikačné číslo spisu:</td><td style=\"padding: 5px;\">" . implode(', ', array_map('htmlspecialchars', $report->FileIdentifierNumber)) . "</td></tr>";
                    } else {
                        echo "<tr><td style=\"padding: 5px; font-weight: bold;\">🔢 Identifikačné číslo spisu:</td><td style=\"padding: 5px;\">" . htmlspecialchars($report->FileIdentifierNumber) . "</td></tr>";
                    }
                }
            }
            
            // Important Dates (if available)
            if ((isset($report->DatesInProceeding) && !empty($report->DatesInProceeding)) || 
                (isset($report->Deadline) && !empty($report->Deadline))) {
                echo "<tr><td colspan=\"2\" style=\"padding: 10px 5px 5px 5px; font-weight: bold; color: #1976d2;\">📅 Dôležité dátumy</td></tr>";
                
                if (isset($report->DatesInProceeding) && !empty($report->DatesInProceeding)) {
                    if (is_array($report->DatesInProceeding)) {
                        echo "<tr><td style=\"padding: 5px; font-weight: bold;\">📆 Dátumy v konaní:</td><td style=\"padding: 5px;\">";
                        foreach ($report->DatesInProceeding as $date) {
                            echo echoDate($date, $json) . "<br>";
                        }
                        echo "</td></tr>";
                    } else {
                        echo "<tr><td style=\"padding: 5px; font-weight: bold;\">📆 Dátumy v konaní:</td><td style=\"padding: 5px;\">" . echoDate($report->DatesInProceeding, $json) . "</td></tr>";
                    }
                }
                
                if (isset($report->Deadline) && !empty($report->Deadline)) {
                    echo "<tr><td style=\"padding: 5px; font-weight: bold;\">⏰ Termín (KRITICKÝ):</td><td style=\"padding: 5px; color: #d32f2f; font-weight: bold;\">" . echoDate($report->Deadline, $json) . "</td></tr>";
                }
            }
            
            // Authority Information (if available)
            if ((isset($report->IssuedBy) && !empty($report->IssuedBy)) || 
                (isset($report->PostedBy) && !empty($report->PostedBy))) {
                echo "<tr><td colspan=\"2\" style=\"padding: 10px 5px 5px 5px; font-weight: bold; color: #1976d2;\">👤 Autoritné osoby</td></tr>";
                
                if (isset($report->IssuedBy) && !empty($report->IssuedBy)) {
                    if (is_array($report->IssuedBy)) {
                        echo "<tr><td style=\"padding: 5px; font-weight: bold;\">✍️ Vydal:</td><td style=\"padding: 5px;\">";
                        foreach ($report->IssuedBy as $person) {
                            if (is_object($person)) {
                                echo htmlspecialchars($person->Name) . " (" . htmlspecialchars($person->Function) . ")<br>";
                            } else {
                                echo htmlspecialchars($person) . "<br>";
                            }
                        }
                        echo "</td></tr>";
                    } else {
                        echo "<tr><td style=\"padding: 5px; font-weight: bold;\">✍️ Vydal:</td><td style=\"padding: 5px;\">" . htmlspecialchars($report->IssuedBy) . "</td></tr>";
                    }
                }
                
                if (isset($report->PostedBy) && !empty($report->PostedBy)) {
                    if (is_array($report->PostedBy)) {
                        echo "<tr><td style=\"padding: 5px; font-weight: bold;\">📮 Zverejnil:</td><td style=\"padding: 5px;\">";
                        foreach ($report->PostedBy as $person) {
                            if (is_object($person)) {
                                echo htmlspecialchars($person->Name) . " (" . htmlspecialchars($person->Function) . ")<br>";
                            } else {
                                echo htmlspecialchars($person) . "<br>";
                            }
                        }
                        echo "</td></tr>";
                    } else {
                        echo "<tr><td style=\"padding: 5px; font-weight: bold;\">📮 Zverejnil:</td><td style=\"padding: 5px;\">" . htmlspecialchars($report->PostedBy) . "</td></tr>";
                    }
                }
            }
            
            // Address Information (if available)
            if (isset($report->DebtorsAddress) && !empty($report->DebtorsAddress)) {
                echo "<tr><td colspan=\"2\" style=\"padding: 10px 5px 5px 5px; font-weight: bold; color: #1976d2;\">📍 Adresa dlžníka</td></tr>";
                echo "<tr><td style=\"padding: 5px;\" colspan=\"2\">" . formatAddress($report->DebtorsAddress, $json) . "</td></tr>";
            }
            
            if (isset($report->ProposersAddress) && !empty($report->ProposersAddress)) {
                echo "<tr><td colspan=\"2\" style=\"padding: 10px 5px 5px 5px; font-weight: bold; color: #1976d2;\">📍 Adresa navrhovateľa</td></tr>";
                echo "<tr><td style=\"padding: 5px;\" colspan=\"2\">" . formatAddress($report->ProposersAddress, $json) . "</td></tr>";
            }
            
            if (isset($report->AdministratorsAddress) && !empty($report->AdministratorsAddress)) {
                echo "<tr><td colspan=\"2\" style=\"padding: 10px 5px 5px 5px; font-weight: bold; color: #1976d2;\">📍 Adresa správcu</td></tr>";
                echo "<tr><td style=\"padding: 5px;\" colspan=\"2\">" . formatAddress($report->AdministratorsAddress, $json) . "</td></tr>";
            }
            
            if (isset($report->CourtsAddress) && !empty($report->CourtsAddress)) {
                echo "<tr><td colspan=\"2\" style=\"padding: 10px 5px 5px 5px; font-weight: bold; color: #1976d2;\">📍 Adresa súdu</td></tr>";
                echo "<tr><td style=\"padding: 5px;\" colspan=\"2\">" . formatAddress($report->CourtsAddress, $json) . "</td></tr>";
            }
            
            echo "</table>";
            echo "</div>";
        }
    } else {
        echo "<p style=\"padding: 15px; background-color: #e8f5e9; border-left: 4px solid #4caf50;\"><i>✅ Žiadne nové zmeny neboli detekované.</i></p>";
    }
    echo "</div>";
}

/**
 * Format address object for display
 * @param mixed $address Address object or array
 * @param bool $json Whether response was in JSON format
 * @return string Formatted HTML address
 */
function formatAddress($address, $json = false)
{
    if (empty($address)) {
        return '<i>Nie je k dispozícii</i>';
    }
    
    $html = '<div style="padding: 10px; background-color: #fff; border: 1px solid #e0e0e0; border-radius: 3px;">';
    
    if (is_array($address)) {
        foreach ($address as $addr) {
            $html .= formatSingleAddress($addr, $json) . '<br>';
        }
    } else {
        $html .= formatSingleAddress($address, $json);
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Format a single address object
 * @param object $addr Address object
 * @param bool $json Whether response was in JSON format
 * @return string Formatted address
 */
function formatSingleAddress($addr, $json = false)
{
    $parts = [];
    
    if (!empty($addr->Name)) {
        $parts[] = '<strong>' . htmlspecialchars($addr->Name) . '</strong>';
    }
    
    if (!empty($addr->BirthDate)) {
        $parts[] = '🎂 ' . echoDate($addr->BirthDate, $json);
    }
    
    if (!empty($addr->Ico)) {
        $parts[] = 'IČO: ' . htmlspecialchars($addr->Ico);
    }
    
    if (!empty($addr->Id)) {
        $parts[] = 'ID: ' . htmlspecialchars($addr->Id);
    }
    
    $addressLine = [];
    if (!empty($addr->Street)) $addressLine[] = htmlspecialchars($addr->Street);
    if (!empty($addr->StreetNumber)) $addressLine[] = htmlspecialchars($addr->StreetNumber);
    if (!empty($addressLine)) $parts[] = implode(' ', $addressLine);
    
    if (!empty($addr->ZipCode) || !empty($addr->City)) {
        $cityLine = [];
        if (!empty($addr->ZipCode)) $cityLine[] = htmlspecialchars($addr->ZipCode);
        if (!empty($addr->City)) $cityLine[] = htmlspecialchars($addr->City);
        $parts[] = implode(' ', $cityLine);
    }
    
    if (!empty($addr->Region)) {
        $parts[] = htmlspecialchars($addr->Region);
    }
    
    if (!empty($addr->Country)) {
        $parts[] = '🌍 ' . htmlspecialchars($addr->Country);
    }
    
    return implode('<br>', $parts);
}

/**
 * Display list of monitored ICOs
 * @param array $response Array of ICO strings
 */
function echoMonitoringList($response)
{
    echo "<pre>";
    echo '<b>List: </b>' . '<br />';
    if (!empty($response)) {
        echo "<ul>";
        foreach ($response as $ico) {
            echo '<li>' . htmlspecialchars($ico) . '</li>';
        }
        echo "</ul>";
    } else {
        echo "<p><i>No companies being monitored.</i></p>";
    }
    echo "</pre>";
}

/**
 * Display API usage limits (safely checks if limits are available)
 * @param FinstatMonitoringApi $api API client instance
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
        echo '<tr>' .
            '<th></th>' .
            '<th>Aktuálny</th>' .
            '<th>MAX</th>' .
            '</tr>';
        echo '<tr>' .
            '<th>Denný</th>' .
            '<th>' . ((isset($limits['daily']) && isset($limits['daily']['current'])) ? $limits['daily']['current'] : "---") . '</th>' .
            '<th>' . ((isset($limits['daily']) && isset($limits['daily']['max'])) ? $limits['daily']['max'] : "---") . '</th>' .
            '</tr>';
        echo '<tr>' .
            '<th>Mesačný</th>' .
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

echo "<h1>👁 FinStat Monitoring API Example</h1>";
echo "<div class='info'><b>ℹ Info:</b> Monitor companies and dates for changes in business registries, financial data, and legal events.</div>";

try {
    // Initialize the API client
    $api = new FinstatMonitoringApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);
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
// EXAMPLE 1: Add Company to Monitoring
// ============================================
echo "<h1>📌 Example 1: Add Company to Monitoring</h1>";
echo "<p><b>Endpoint:</b> AddToMonitoring('$testIco')</p>";

try {
    $result = $api->AddToMonitoring($testIco, $useJson);
    echo "<div class='success'>✓ Company added to monitoring: ICO $testIco</div>";
    echoLimitsSafe($api);
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>⚠ Company Not Found</h3>";
    echo "<p>ICO: " . htmlspecialchars($e->getRequestParameter()) . "</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (LimitReachedException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>⛔ API Limit Reached</h3>";
    echo "<p><b>Daily:</b> {$e->getDailyCurrent()} / {$e->getDailyMax()}</p>";
    echo "<p><b>Monthly:</b> {$e->getMonthlyCurrent()} / {$e->getMonthlyMax()}</p>";
    echo "</div>";
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
}

echo '<hr />';

// ============================================
// EXAMPLE 2: Add Date to Monitoring
// ============================================
echo "<h1>📅 Example 2: Add Date to Monitoring</h1>";
echo "<p><b>Endpoint:</b> AddDateToMonitoring('$testDate')</p>";

try {
    $result = $api->AddDateToMonitoring($testDate, $useJson);
    echo "<div class='success'>✓ Date added to monitoring: $testDate</div>";
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

// ============================================
// EXAMPLE 3: Get Monitoring Lists
// ============================================
echo "<h1>📋 Example 3: Get Monitoring Lists</h1>";

echo "<h2>Companies Being Monitored:</h2>";
try {
    $list = $api->MonitoringList($useJson);
    echoMonitoringList($list);
    echoLimitsSafe($api);
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
}

echo "<h2>Dates Being Monitored:</h2>";
try {
    $list = $api->MonitoringDateList($useJson);
    echoMonitoringList($list);
    echoLimitsSafe($api);
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
}

echo '<hr />';

// ============================================
// EXAMPLE 4: Get Monitoring Reports
// ============================================
echo "<h1>📊 Example 4: Get Monitoring Reports (Detected Changes)</h1>";

echo "<h2>Company Changes Report:</h2>";
try {
    $report = $api->MonitoringReport($useJson);
    echoMonitoringReport($report, $useJson);
    echoLimitsSafe($api);
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
}

echo "<h2>Date Changes Report:</h2>";
try {
    $report = $api->MonitoringDateReport($useJson);
    echoMonitoringReport($report, $useJson);
    echoLimitsSafe($api);
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
}

echo '<hr />';

// ============================================
// EXAMPLE 5: Remove from Monitoring
// ============================================
echo "<h1>🗑 Example 5: Remove from Monitoring</h1>";

echo "<h2>Remove Company:</h2>";
try {
    $result = $api->RemoveFromMonitoring($testIco, $useJson);
    echo "<div class='success'>✓ Company removed from monitoring: ICO $testIco</div>";
    echoLimitsSafe($api);
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>⚠ Company Not in Monitoring List</h3>";
    echo "<p>ICO: " . htmlspecialchars($e->getRequestParameter()) . "</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
}

echo "<h2>Remove Date:</h2>";
try {
    $result = $api->RemoveDateFromMonitoring($testDate, $useJson);
    echo "<div class='success'>✓ Date removed from monitoring: $testDate</div>";
    echoLimitsSafe($api);
} catch (FinstatException $e) {
    echoException($e);
    echoLimitsSafe($api);
}

echo '<hr />';
echo "<div class='info'><b>✓ Complete:</b> All monitoring API examples executed successfully.</div>";
