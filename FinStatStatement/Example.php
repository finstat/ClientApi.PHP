<?php
/**
 * Example: FinStat Statement API Usage
 *
 * This example demonstrates how to:
 * - List the financial statements of a company and the templates each one can be shown in
 * - Request one statement in one of those templates
 * - Request the legend (row names) of a template
 * - Handle API exceptions properly
 *
 * @package FinStat
 * @author FinStat s.r.o.
 * @link https://www.finstat.sk/
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Import required classes
use FinStat\Api\FinstatStatementApi;
use FinStat\Client\Exceptions\NotFoundException;
use FinStat\Client\Exceptions\LimitReachedException;
use FinStat\Client\Exceptions\AuthenticationException;
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
$lang = 'sk';

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Template name of a template value: XML sends names, JSON sends the server's numbers
 * @param mixed $template Template name or number
 * @return string Template name
 */
function templateName($template)
{
    $names = array(
        1 => 'Template2011v2',
        2 => 'Template2014',
        3 => 'Template2014micro',
        4 => 'TemplateFinancial',
        8 => 'TemplateROPO',
        9 => 'TemplateNujPU',
    );
    return is_numeric($template) && isset($names[(int)$template]) ? $names[(int)$template] : (string)$template;
}

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
    echo "<p><b>" . get_class($e) . "</b> ({$e->getCode()}): " . htmlspecialchars($e->getMessage()) . "</p>";
    if ($e instanceof LimitReachedException) {
        echo "<p><b>Daily:</b> {$e->getDailyCurrent()} / {$e->getDailyMax()}, <b>Monthly:</b> {$e->getMonthlyCurrent()} / {$e->getMonthlyMax()}</p>";
    }
    echo "</div>";
}

/**
 * Display the rows of one statement section
 * @param string $title Section title
 * @param array $rows Statement or legend rows
 * @param array $names Row names from the legend, by row number
 */
function echoRows($title, $rows, $names = array())
{
    echo "<h3>" . htmlspecialchars($title) . "</h3>";
    if (empty($rows)) {
        echo "<p><i>No rows.</i></p>";
        return;
    }
    echo "<table><tr><th>Row</th><th>Section</th><th>Name</th><th>Actual</th><th>Previous</th></tr>";
    foreach (array_slice($rows, 0, 15) as $row) {
        $name = isset($row->Name) ? $row->Name : (isset($names[$row->ReportRow]) ? $names[$row->ReportRow] : '');
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row->ReportRow) . "</td>";
        echo "<td>" . htmlspecialchars($row->ReportSection) . "</td>";
        echo "<td>" . htmlspecialchars($name) . "</td>";
        echo "<td>" . (isset($row->Actual) ? number_format($row->Actual, 0, ',', ' ') : '') . "</td>";
        echo "<td>" . (isset($row->Previous) ? number_format($row->Previous, 0, ',', ' ') : '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    if (count($rows) > 15) {
        echo "<p><i>… " . (count($rows) - 15) . " more rows</i></p>";
    }
}

// ============================================
// API CLIENT INITIALIZATION
// ============================================

header('Content-Type: text/html; charset=utf-8');

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #2196F3; color: white; }
h1 { color: #333; border-bottom: 2px solid #2196F3; padding-bottom: 10px; }
h3 { color: #2196F3; }
.success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; margin: 10px 0; }
.info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 10px; margin: 10px 0; }
.warning { background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 10px; margin: 10px 0; }
hr { margin: 30px 0; border: none; border-top: 2px solid #2196F3; }
</style>";

echo "<h1>📈 FinStat Statement API Example</h1>";
echo "<div class='info'><b>ℹ Info:</b> Financial statements (balance sheet, income statement) of Slovak companies, in the templates they can be converted to.</div>";

try {
    $api = new FinstatStatementApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);
    echo "<div class='success'>✓ API client initialized successfully</div>";
} catch (AuthenticationException $e) {
    echo "<div class='warning'><h2 style='margin-top: 0;'>❌ Authentication Failed</h2>";
    echo "<p>Please configure your API credentials in the CONFIGURATION section.</p>";
    echo "<p><b>Error:</b> " . htmlspecialchars($e->getMessage()) . "</p></div>";
    exit(1);
} catch (Exception $e) {
    echo "<div class='warning'><h2 style='margin-top: 0;'>❌ Fatal Error</h2>";
    echo "<p><b>Error:</b> " . htmlspecialchars($e->getMessage()) . "</p></div>";
    exit(1);
}

// ============================================
// EXAMPLE 1: List of Statements
// ============================================
echo "<h1>📋 Example 1: List of Statements</h1>";
echo "<p><b>Endpoint:</b> RequestStatements('$testIco')</p>";

$statements = null;
try {
    $statements = $api->RequestStatements($testIco, $useJson);
    echo "<table><tr><th>Year</th><th>From</th><th>To</th><th>Published</th><th>Templates</th></tr>";
    foreach ((array)$statements as $s) {
        echo "<tr>";
        echo "<td>{$s->Year}</td>";
        echo "<td>" . echoDate($s->DateFrom, $useJson) . "</td>";
        echo "<td>" . echoDate($s->DateTo, $useJson) . "</td>";
        echo "<td>" . echoDate($s->DatePublished, $useJson) . "</td>";
        echo "<td>" . htmlspecialchars(implode(', ', array_map('templateName', (array)$s->Templates))) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (NotFoundException $e) {
    echo "<div class='warning'>⚠ No statements found for ICO " . htmlspecialchars($testIco) . "</div>";
} catch (FinstatException $e) {
    echoException($e);
}

echo '<hr />';

// The latest statement, in the first template the list offers for it
$latest = !empty($statements) ? $statements[0] : null;
$template = ($latest && !empty($latest->Templates)) ? templateName($latest->Templates[0]) : 'Template2014';
$isNonProfit = in_array($template, array('TemplateROPO', 'TemplateNujPU'));

// ============================================
// EXAMPLE 2: Template Legend
// ============================================
echo "<h1>📖 Example 2: Legend of $template</h1>";
echo "<p><b>Endpoint:</b> RequestStatementLegend('$template', '$lang')</p>";

$names = array();
try {
    $legend = $api->RequestStatementLegend($template, $lang, $useJson);
    foreach (array('Assets', 'LiabilitiesAndEquity', 'IncomeStatement', 'Expenses', 'Revenue') as $section) {
        foreach ((array)($legend->$section ?? array()) as $row) {
            $names[$section][$row->ReportRow] = $row->Name;
        }
    }
    echoRows('Assets (first rows)', $legend->Assets);
} catch (FinstatException $e) {
    echoException($e);
}

echo '<hr />';

// ============================================
// EXAMPLE 3: Statement Detail
// ============================================
if ($latest) {
    echo "<h1>📊 Example 3: Statement {$latest->Year} in $template</h1>";
    echo "<p><b>Endpoint:</b> RequestStatementDetail('$testIco', {$latest->Year}, '$template')</p>";

    try {
        $detail = $api->RequestStatementDetail($testIco, $latest->Year, $template, $useJson);
        echo "<p><b>Company:</b> " . htmlspecialchars($detail->Name) . " (" . htmlspecialchars($detail->ICO) . ")</p>";
        echo "<p><b>Format:</b> " . htmlspecialchars(templateName($detail->Format)) . ", <b>Source:</b> " . htmlspecialchars($detail->Source) . "</p>";
        echoRows('Assets', $detail->Assets, $names['Assets'] ?? array());
        echoRows('Liabilities and Equity', $detail->LiabilitiesAndEquity, $names['LiabilitiesAndEquity'] ?? array());
        if ($isNonProfit) {
            echoRows('Expenses', $detail->Expenses, $names['Expenses'] ?? array());
            echoRows('Revenue', $detail->Revenue, $names['Revenue'] ?? array());
        } else {
            echoRows('Income Statement', $detail->IncomeStatement, $names['IncomeStatement'] ?? array());
        }
    } catch (NotFoundException $e) {
        echo "<div class='warning'>⚠ The statement cannot be shown in $template: " . htmlspecialchars($e->getMessage()) . "</div>";
    } catch (FinstatException $e) {
        echoException($e);
    }

    echo '<hr />';
}

echo "<div class='success'><b>✓ Complete:</b> Statement API examples executed.</div>";
