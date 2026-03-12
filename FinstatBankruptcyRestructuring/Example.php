<?php
/**
 * Example: FinStat Bankruptcy & Restructuring API Usage
 * 
 * This example demonstrates how to:
 * - Search for personal bankruptcy proceedings by name and birth date
 * - Search for company bankruptcy/restructuring by ICO
 * - Search for company bankruptcy/restructuring by name
 * - View detailed bankruptcy information (debtors, states, deadlines)
 * - Handle API exceptions properly
 * 
 * The API provides access to official bankruptcy and restructuring proceedings
 * from the Slovak Insolvency Register (ISIR).
 * 
 * @package FinStat
 * @author FinStat s.r.o.
 * @link https://www.finstat.sk/
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Import required classes
use FinStat\Api\FinstatBankruptcyRestructuringApi;
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
$testFirstName = 'peter';
$testLastName = 'toth';
$testBirthDate = '1988-08-16';
$testIco = '36381250';
$testCompanyName = 'talise';

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
 * Display list of bankruptcy/restructuring proceedings
 * @param array $response Array of bankruptcy/restructuring objects
 * @param bool $json Whether response was in JSON format
 */
function echoBankruptcyRestructuringList($response, $json = false)
{
    echo "<pre style=\"background-color: #f5f5f5; padding: 15px; border-left: 3px solid #f44336;\">";
    
    if (!empty($response)) {
        echo "<table>";
        echo "<tr>";
        echo "<th>Debtors</th>";
        echo "<th>File Reference</th>";
        echo "<th>First Record</th>";
        echo "<th>Last Record</th>";
        echo "<th>RU State</th>";
        echo "<th>OV State</th>";
        echo "<th>Enter Date</th>";
        echo "<th>Exit Date</th>";
        echo "<th>End State</th>";
        echo "<th>End Reason</th>";
        echo "<th>Deadlines</th>";
        echo "<th>FinStat URL</th>";
        echo "</tr>";
        
        foreach ($response as $data) {
            echo "<tr>";
            
            // Debtors
            echo "<td>";
            if (!empty($data->Debtors)) {
                foreach ($data->Debtors as $debtor) {
                    echo htmlspecialchars($debtor->Name) . "<br />";
                }
            }
            echo "</td>";
            
            // File Reference
            echo "<td>" . htmlspecialchars($data->FileReference) . "</td>";
            
            // Dates
            echo "<td>" . echoDate($data->FirstRecordDate, $json) . "</td>";
            echo "<td>" . echoDate($data->LastRecordDate, $json) . "</td>";
            
            // States
            echo "<td>" . htmlspecialchars($data->RUState) . " " . echoDate($data->RUStateDate, $json) . "</td>";
            echo "<td>" . htmlspecialchars($data->OVState) . " " . echoDate($data->OVStateDate, $json) . "</td>";
            
            // Entry/Exit
            echo "<td>" . echoDate($data->EnterDate, $json) . "</td>";
            echo "<td>" . echoDate($data->ExitDate, $json) . "</td>";
            
            // End information
            echo "<td>" . htmlspecialchars($data->EndState) . "</td>";
            echo "<td>" . htmlspecialchars($data->EndReason) . "</td>";
            
            // Deadlines
            echo "<td>";
            if (!empty($data->Deadlines)) {
                foreach ($data->Deadlines as $deadline) {
                    echo htmlspecialchars($deadline->Type) . ": " . echoDate($deadline->Date, $json) . "<br />";
                }
            }
            echo "</td>";
            
            // URL
            echo "<td><a href=\"" . htmlspecialchars($data->FinstatURL) . "\" target=\"_blank\">View Details</a></td>";
            
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p><i>No bankruptcy or restructuring proceedings found.</i></p>";
    }
    
    echo "</pre>";
}

/**
 * Display API usage limits (safely checks if limits are available)
 * @param FinstatBankruptcyRestructuringApi $api API client instance
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
        echo '<tr><th></th><th>Aktuálny</th><th>MAX</th></tr>';
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
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
th { background-color: #f44336; color: white; }
h1 { color: #333; border-bottom: 2px solid #f44336; padding-bottom: 10px; }
h2 { color: #555; }
pre { background-color: #f5f5f5; padding: 15px; border-left: 3px solid #f44336; }
.success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; margin: 10px 0; }
.info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 10px; margin: 10px 0; }
.warning { background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 10px; margin: 10px 0; }
hr { margin: 30px 0; border: none; border-top: 2px solid #f44336; }
a { color: #f44336; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>";

echo "<h1>⚖ FinStat Bankruptcy & Restructuring API Example</h1>";
echo "<div class='info'><b>ℹ Info:</b> Search for bankruptcy and restructuring proceedings from the Slovak Insolvency Register (ISIR).</div>";

try {
    // Initialize the API client
    $api = new FinstatBankruptcyRestructuringApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);
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
// EXAMPLE 1: Search Personal Bankruptcy
// ============================================
echo "<h1>👤 Example 1: Personal Bankruptcy Proceedings</h1>";
echo "<p><b>Endpoint:</b> RequestPersonBankruptcyProceedings('$testFirstName', '$testLastName', '$testBirthDate')</p>";
echo "<p>Search for bankruptcy proceedings of individuals by name and birth date.</p>";

try {
    $birthDate = new DateTime($testBirthDate);
    $data = $api->RequestPersonBankruptcyProceedings($testFirstName, $testLastName, $birthDate, $useJson);
    
    if (!empty($data)) {
        echo "<div class='success'>✓ Found " . count($data) . " proceeding(s) for: $testFirstName $testLastName (born " . $birthDate->format('d.m.Y') . ")</div>";
        echoBankruptcyRestructuringList($data, $useJson);
    } else {
        echo "<div class='info'>ℹ No bankruptcy proceedings found for: $testFirstName $testLastName (born " . $birthDate->format('d.m.Y') . ")</div>";
    }
    
    echoLimitsSafe($api);
    
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>⚠ No Results Found</h3>";
    echo "<p>No bankruptcy proceedings found for: $testFirstName $testLastName (born $testBirthDate)</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (BadRequestException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>❌ Bad Request</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check the input parameters (name, birth date).</p>";
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
// EXAMPLE 2: Search Company Bankruptcy by ICO
// ============================================
echo "<h1>🏢 Example 2: Company Bankruptcy/Restructuring by IČO</h1>";
echo "<p><b>Endpoint:</b> RequestCompanyBankruptcyRestructuring('$testIco', null)</p>";
echo "<p>Search for bankruptcy and restructuring proceedings of companies by their IČO (company ID).</p>";

try {
    $data = $api->RequestCompanyBankruptcyRestructuring($testIco, null, $useJson);
    
    if (!empty($data)) {
        echo "<div class='success'>✓ Found " . count($data) . " proceeding(s) for IČO: $testIco</div>";
        echoBankruptcyRestructuringList($data, $useJson);
    } else {
        echo "<div class='info'>ℹ No bankruptcy or restructuring proceedings found for IČO: $testIco</div>";
    }
    
    echoLimitsSafe($api);
    
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>⚠ No Results Found</h3>";
    echo "<p>No bankruptcy or restructuring proceedings found for IČO: $testIco</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (BadRequestException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>❌ Bad Request</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check the IČO format.</p>";
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
// EXAMPLE 3: Search Company Bankruptcy by Name
// ============================================
echo "<h1>🔍 Example 3: Company Bankruptcy/Restructuring by Name</h1>";
echo "<p><b>Endpoint:</b> RequestCompanyBankruptcyRestructuring(null, '$testCompanyName')</p>";
echo "<p>Search for bankruptcy and restructuring proceedings of companies by their name (partial match).</p>";

try {
    $data = $api->RequestCompanyBankruptcyRestructuring(null, $testCompanyName, $useJson);
    
    if (!empty($data)) {
        echo "<div class='success'>✓ Found " . count($data) . " proceeding(s) matching: '$testCompanyName'</div>";
        echoBankruptcyRestructuringList($data, $useJson);
    } else {
        echo "<div class='info'>ℹ No bankruptcy or restructuring proceedings found matching: '$testCompanyName'</div>";
    }
    
    echoLimitsSafe($api);
    
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>⚠ No Results Found</h3>";
    echo "<p>No bankruptcy or restructuring proceedings found matching: '$testCompanyName'</p>";
    echo "</div>";
    echoLimitsSafe($api);
} catch (BadRequestException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>❌ Bad Request</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please provide a valid search term.</p>";
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
// INFORMATION SECTION
// ============================================
echo "<div class='info'>";
echo "<h2 style='margin-top: 0;'>ℹ About Bankruptcy & Restructuring Data</h2>";
echo "<p><b>Data Source:</b> Information is retrieved from the official Slovak Insolvency Register (ISIR - Insolvency Register of the Slovak Republic).</p>";
echo "<p><b>Coverage:</b> Includes both personal bankruptcy proceedings and company bankruptcy/restructuring proceedings.</p>";
echo "<p><b>Search Options:</b></p>";
echo "<ul>";
echo "<li><b>Personal:</b> Search by first name, last name, and birth date</li>";
echo "<li><b>Company by IČO:</b> Search by exact company identification number</li>";
echo "<li><b>Company by Name:</b> Search by company name (supports partial matching)</li>";
echo "</ul>";
echo "<p><b>Information Included:</b></p>";
echo "<ul>";
echo "<li>Debtor information (names)</li>";
echo "<li>File reference numbers</li>";
echo "<li>Record dates (first and last)</li>";
echo "<li>Restructuring (RU) and Insolvency (OV) states with dates</li>";
echo "<li>Entry and exit dates from register</li>";
echo "<li>End state and reason</li>";
echo "<li>Important deadlines by type</li>";
echo "<li>Direct links to FinStat for detailed information</li>";
echo "</ul>";
echo "<p><b>Legal Note:</b> This data is publicly available from official registers. Always verify critical information with official sources.</p>";
echo "</div>";

echo '<hr />';
echo "<div class='success'><b>✓ Complete:</b> Bankruptcy & Restructuring API examples executed successfully.</div>";
