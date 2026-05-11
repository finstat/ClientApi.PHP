<?php
/**
 * Example: FinStat Reporting API Usage
 * 
 * This example demonstrates how to:
 * - Request list of available report topics
 * - View available reports for each topic
 * - Download report files with company data
 * - Display report metadata (descriptions, counts, dates)
 * - Handle API exceptions properly
 * 
 * The Reporting API provides access to pre-generated reports containing
 * aggregated company data organized by various topics and categories.
 * 
 * @package FinStat
 * @author FinStat s.r.o.
 * @link https://www.finstat.sk/
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Import required classes
use FinStat\Api\FinstatReportingApi;
use FinStat\Client\Exceptions\NotFoundException;
use FinStat\Client\Exceptions\LimitReachedException;
use FinStat\Client\Exceptions\AuthenticationException;
use FinStat\Client\Exceptions\BadRequestException;
use FinStat\Client\Exceptions\UnauthorizedException;
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
 * Display list of report topics
 * @param array $topics Array of topic objects
 */
function echoTopics($topics)
{
    echo "<pre style=\"background-color: #f5f5f5; padding: 15px; border-left: 3px solid #2196F3;\">";
    echo '<b>Available Topics:</b><br /><br />';
    
    if (!empty($topics)) {
        echo "<table>";
        echo "<tr>";
        echo "<th>Topic ID</th>";
        echo "<th>Topic Name</th>";
        echo "<th>Group</th>";
        echo "</tr>";
        
        foreach ($topics as $topic) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($topic->ID) . "</td>";
            echo "<td>" . htmlspecialchars($topic->Name) . "</td>";
            echo "<td>" . htmlspecialchars($topic->Group) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p><i>No topics available.</i></p>";
    }
    
    echo "</pre>";
}

/**
 * Display list of reports for a specific topic
 * @param array $reports Array of report objects
 * @param object $topic Topic object
 * @param bool $json Whether response was in JSON format
 */
function echoReports($reports, $topic, $json = false)
{
    echo "<h3 style=\"color: #2196F3;\">📑 Reports for: " . htmlspecialchars($topic->Name) . "</h3>";
    echo "<pre style=\"background-color: #f5f5f5; padding: 15px; border-left: 3px solid #2196F3;\">";
    
    if (!empty($reports)) {
        echo "<table>";
        echo "<tr>";
        echo "<th>File Name</th>";
        echo "<th>Description</th>";
        echo "<th>Topic</th>";
        echo "<th>Group</th>";
        echo "<th>Count</th>";
        echo "<th>Date</th>";
        echo "<th>Action</th>";
        echo "</tr>";
        
        foreach ($reports as $report) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($report->FileName) . "</td>";
            echo "<td>" . htmlspecialchars($report->Description) . "</td>";
            echo "<td>" . htmlspecialchars($report->Topic) . "</td>";
            echo "<td>" . htmlspecialchars($report->Group) . "</td>";
            echo "<td>" . htmlspecialchars($report->Count) . "</td>";
            echo "<td>" . (($report->Date) ? echoDate($report->Date, $json) : 'N/A') . "</td>";
            echo "<td><a href=\"?file=" . urlencode($report->FileName) . "\">Download</a></td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p><i>No reports available for this topic.</i></p>";
    }
    
    echo "</pre>";
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
h3 { color: #2196F3; }
pre { background-color: #f5f5f5; padding: 15px; border-left: 3px solid #2196F3; }
.success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; margin: 10px 0; }
.info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 10px; margin: 10px 0; }
.warning { background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 10px; margin: 10px 0; }
hr { margin: 30px 0; border: none; border-top: 2px solid #2196F3; }
a { color: #2196F3; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>";

echo "<h1>📊 FinStat Reporting API Example</h1>";
echo "<div class='info'><b>ℹ Info:</b> Access pre-generated reports containing aggregated company data organized by topics and categories.</div>";

try {
    // Initialize the API client
    $api = new FinstatReportingApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);
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
// EXAMPLE 1: Download Specific File (if requested)
// ============================================
$file = (isset($_GET['file']) && !empty($_GET['file'])) ? $_GET['file'] : null;

if (!empty($file)) {
    echo "<h1>⬇ Downloading Report: " . htmlspecialchars($file) . "</h1>";
    
    try {
        // Download the file (saves with .xlsx extension)
        $outputFile = $file . '.xlsx';
        $data = $api->DownloadReportFile($file, $outputFile);
        
        if ($data) {
            $fileSize = file_exists($outputFile) ? filesize($outputFile) : 0;
            echo "<div class='success'>";
            echo "<h2 style='margin-top: 0;'>✓ Report Downloaded Successfully</h2>";
            echo "<p><b>File Name:</b> " . htmlspecialchars($outputFile) . "</p>";
            echo "<p><b>File Size:</b> " . number_format($fileSize) . " bytes</p>";
            echo "<p><b>Saved to:</b> " . htmlspecialchars(realpath($outputFile)) . "</p>";
            echo "<p><b>Format:</b> Excel (.xlsx)</p>";
            echo "</div>";
        } else {
            echo "<div class='warning'>";
            echo "<h3 style='margin-top: 0;'>⚠ Download Failed</h3>";
            echo "<p>No data received from API.</p>";
            echo "</div>";
        }
        
    } catch (NotFoundException $e) {
        echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px;'>";
        echo "<h3 style='margin-top: 0;'>⚠ File Not Found</h3>";
        echo "<p>The requested report does not exist or is no longer available.</p>";
        echo "<p><b>File:</b> " . htmlspecialchars($file) . "</p>";
        echo "</div>";
    } catch (LimitReachedException $e) {
        echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px;'>";
        echo "<h3 style='margin-top: 0;'>⛔ API Limit Reached</h3>";
        echo "<p><b>Daily:</b> {$e->getDailyCurrent()} / {$e->getDailyMax()}</p>";
        echo "<p><b>Monthly:</b> {$e->getMonthlyCurrent()} / {$e->getMonthlyMax()}</p>";
        echo "</div>";
    } catch (FinstatException $e) {
        echoException($e);
    }
    
    echo '<hr />';
}

// ============================================
// EXAMPLE 2: List Available Topics
// ============================================
echo "<h1>📋 Example 1: List of Available Report Topics</h1>";
echo "<p><b>Endpoint:</b> RequestTopics()</p>";
echo "<p>Retrieves a list of all available report topics with their IDs and groups.</p>";

try {
    $topics = $api->RequestTopics($useJson);
    
    if ($topics !== null && !empty($topics)) {
        echoTopics($topics);
        
        echo "<div class='info'>";
        echo "<b>Total Topics:</b> " . count($topics);
        echo "</div>";
    } else {
        echo "<div class='warning'>";
        echo "<h3 style='margin-top: 0;'>⚠ No Topics</h3>";
        echo "<p>No report topics are currently available.</p>";
        echo "</div>";
    }
    
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>⚠ No Topics Found</h3>";
    echo "<p>No report topics are currently available.</p>";
    echo "</div>";
} catch (LimitReachedException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>⛔ API Limit Reached</h3>";
    echo "<p><b>Daily:</b> {$e->getDailyCurrent()} / {$e->getDailyMax()}</p>";
    echo "<p><b>Monthly:</b> {$e->getMonthlyCurrent()} / {$e->getMonthlyMax()}</p>";
    echo "</div>";
} catch (UnauthorizedException $e) {
    // HTTP 401 — the caller's licence does not cover editing this monitoring topic.
    // Server message (SK): "Nemáte platnú licenciu pre editáciu tohto monitoringu!"
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>🚫 Unauthorized for this topic</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
} catch (FinstatException $e) {
    echoException($e);
}

echo '<hr />';

// ============================================
// EXAMPLE 3: List Reports for Each Topic
// ============================================
echo "<h1>📑 Example 2: List of Available Reports by Topic</h1>";
echo "<p><b>Endpoint:</b> RequestList(topicID)</p>";
echo "<p>Retrieves all available reports for each topic. Click on any file name to download it.</p>";

if (isset($topics) && !empty($topics)) {
    $totalReports = 0;
    
    foreach ($topics as $topic) {
        try {
            $reports = $api->RequestList($topic->ID, $useJson);
            
            if ($reports !== null && !empty($reports)) {
                echoReports($reports, $topic, $useJson);
                $totalReports += count($reports);
            } else {
                echo "<h3 style=\"color: #999;\">📑 Reports for: " . htmlspecialchars($topic->Name) . "</h3>";
                echo "<p style=\"margin-left: 20px; color: #999;\"><i>No reports available for this topic.</i></p>";
            }
            
        } catch (NotFoundException $e) {
            echo "<h3 style=\"color: #999;\">📑 Reports for: " . htmlspecialchars($topic->Name) . "</h3>";
            echo "<p style=\"margin-left: 20px; color: #999;\"><i>No reports found for this topic.</i></p>";
        } catch (LimitReachedException $e) {
            echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px;'>";
            echo "<h3 style='margin-top: 0;'>⛔ API Limit Reached</h3>";
            echo "<p><b>Daily:</b> {$e->getDailyCurrent()} / {$e->getDailyMax()}</p>";
            echo "<p><b>Monthly:</b> {$e->getMonthlyCurrent()} / {$e->getMonthlyMax()}</p>";
            echo "</div>";
            break; // Stop processing if limit is reached
        } catch (FinstatException $e) {
            echo "<h3 style=\"color: #f44336;\">⚠ Error processing topic: " . htmlspecialchars($topic->Name) . "</h3>";
            echoException($e);
        }
    }
    
    echo "<div class='success'>";
    echo "<b>Summary:</b> $totalReports report(s) available across " . count($topics) . " topic(s)";
    echo "</div>";
    
} else {
    echo "<div class='warning'>";
    echo "<h3 style='margin-top: 0;'>⚠ No Topics Available</h3>";
    echo "<p>Cannot list reports because no topics were found.</p>";
    echo "</div>";
}

echo '<hr />';

// ============================================
// INFORMATION SECTION
// ============================================
echo "<div class='info'>";
echo "<h2 style='margin-top: 0;'>ℹ About Reporting API</h2>";
echo "<p><b>Purpose:</b> The Reporting API provides access to pre-generated reports containing aggregated company data.</p>";
echo "<p><b>Topics:</b> Reports are organized by topics (e.g., industry sectors, financial categories, geographical regions).</p>";
echo "<p><b>Format:</b> Reports are typically delivered as Excel files (.xlsx) with structured data ready for analysis.</p>";
echo "<p><b>Usage:</b> Download reports to perform bulk analysis, market research, or data integration without individual API calls.</p>";
echo "<p><b>Updates:</b> Reports are generated periodically and contain the most recent data available at generation time.</p>";
echo "</div>";

echo '<hr />';
echo "<div class='success'><b>✓ Complete:</b> Reporting API examples executed successfully.</div>";
