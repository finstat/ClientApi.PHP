<?php
/**
 * Example: FinStat Daily Diff API Usage
 * 
 * This example demonstrates how to:
 * - Request list of available daily difference files
 * - View metadata for each file (size, dates, version)
 * - Download daily difference files containing company changes
 * - Handle API exceptions properly
 * 
 * Daily Diff files contain changes to company data that occurred on specific dates,
 * allowing you to track modifications to the business register.
 * 
 * @package FinStat
 * @author FinStat s.r.o.
 * @link https://www.finstat.sk/
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Import required classes
use FinStat\Api\FinstatDailyDiffApi;
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
 * Display list of daily diff files with metadata
 * @param object $list Daily diff list result object
 * @param bool $json Whether response was in JSON format
 */
function echoDailyDiffList($list, $json = false)
{
    echo "<pre style=\"background-color: #f5f5f5; padding: 15px; border-left: 3px solid #2196F3;\">";
    echo '<b>API Version:</b> ' . htmlspecialchars($list->Version) . '<br /><br />';
    
    if (!empty($list->Files)) {
        echo '<b>Available Files:</b><br />';
        echo "<table>";
        echo "<tr>";
        echo "<th>File Name</th>";
        echo "<th>Generated Date</th>";
        echo "<th>File Size</th>";
        echo "<th>Upload Date</th>";
        echo "<th>Action</th>";
        echo "</tr>";
        
        foreach ($list->Files as $file) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($file->FileName) . "</td>";
            echo "<td>" . (($file->GeneratedDate) ? echoDate($file->GeneratedDate, $json) : 'N/A') . "</td>";
            echo "<td>" . number_format($file->FileSize) . " bytes</td>";
            echo "<td>" . (($file->UploadDate) ? echoDate($file->UploadDate, $json) : 'N/A') . "</td>";
            echo "<td><a href=\"?file=" . urlencode($file->FileName) . "\">Download</a></td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p><i>No files available.</i></p>";
    }
    
    echo "</pre>";
}

/**
 * Format file size for display
 * @param int $bytes File size in bytes
 * @return string Formatted file size
 */
function formatFileSize($bytes)
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
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
.warning { background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 10px; margin: 10px 0; }
hr { margin: 30px 0; border: none; border-top: 2px solid #2196F3; }
a { color: #2196F3; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>";

echo "<h1>📊 FinStat Daily Diff API Example</h1>";
echo "<div class='info'><b>ℹ Info:</b> Download daily difference files containing changes to company data in the business register.</div>";

try {
    // Initialize the API client
    $api = new FinstatDailyDiffApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);
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
    echo "<h1>⬇ Downloading File: " . htmlspecialchars($file) . "</h1>";
    
    try {
        // Download the file (saves to current directory with same filename)
        $data = $api->DownloadDailyDiffFile($file, $file);
        
        if ($data) {
            $fileSize = file_exists($file) ? filesize($file) : 0;
            echo "<div class='success'>";
            echo "<h2 style='margin-top: 0;'>✓ File Downloaded Successfully</h2>";
            echo "<p><b>File Name:</b> " . htmlspecialchars($file) . "</p>";
            echo "<p><b>File Size:</b> " . formatFileSize($fileSize) . "</p>";
            echo "<p><b>Saved to:</b> " . htmlspecialchars(realpath($file)) . "</p>";
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
        echo "<p>The requested file does not exist or is no longer available.</p>";
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
// EXAMPLE 2: List Available Daily Diff Files
// ============================================
echo "<h1>📋 Example: List of Available Daily Diff Files</h1>";
echo "<p><b>Endpoint:</b> RequestListOfDailyDiffs()</p>";
echo "<p>Retrieves a list of all available daily difference files with metadata. Click on any file name to download it.</p>";

try {
    $list = $api->RequestListOfDailyDiffs($useJson);
    
    if ($list !== null) {
        echoDailyDiffList($list, $useJson);
        
        // Display summary
        if (!empty($list->Files)) {
            $totalFiles = count($list->Files);
            $totalSize = array_sum(array_map(function($f) { return $f->FileSize; }, $list->Files));
            
            echo "<div class='info'>";
            echo "<b>Summary:</b> $totalFiles file(s) available, total size: " . formatFileSize($totalSize);
            echo "</div>";
        }
    } else {
        echo "<div class='warning'>";
        echo "<h3 style='margin-top: 0;'>⚠ No Data</h3>";
        echo "<p>API returned no data. This might indicate no files are currently available.</p>";
        echo "</div>";
    }
    
} catch (NotFoundException $e) {
    echo "<div style='background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px;'>";
    echo "<h3 style='margin-top: 0;'>⚠ No Files Found</h3>";
    echo "<p>No daily diff files are currently available.</p>";
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

// ============================================
// INFORMATION SECTION
// ============================================
echo "<div class='info'>";
echo "<h2 style='margin-top: 0;'>ℹ About Daily Diff Files</h2>";
echo "<p><b>Purpose:</b> Daily diff files contain incremental changes to company data, allowing you to keep your local database synchronized with the business register.</p>";
echo "<p><b>Content:</b> Each file includes companies that were created, modified, or deleted on a specific date.</p>";
echo "<p><b>Format:</b> Files are typically in CSV or XML format and can be processed programmatically.</p>";
echo "<p><b>Usage:</b> Download files regularly to maintain up-to-date company information without requesting full datasets.</p>";
echo "</div>";

echo '<hr />';
echo "<div class='success'><b>✓ Complete:</b> Daily diff API examples executed successfully.</div>";
