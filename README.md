# Finstat API Client for PHP

This repository contains a PHP client for interacting with the Finstat API services. Finstat provides financial and corporate data for businesses in Slovakia and the Czech Republic.

## Features

- Retrieve detailed company information by ICO (Company ID)
- Search companies using autocomplete functionality
- Monitor changes in company data
- Access daily data diffs
- Support for both Slovak and Czech business registries

## Installation

```bash
composer require finstat/client-api
```

## Configuration

Before using the API client, you need to set up your credentials:

```php
// Basic API configuration
$apiUrl = 'https://www.finstat.sk/api/';    // URL for Slovak API (use 'https://cz.finstat.sk/api/' for Czech API)
$apiKey = 'YOUR_API_KEY';                  // Your unique API key
$privateKey = 'YOUR_PRIVATE_KEY';          // Your private key
$stationId = 'Your Station ID';            // Identifier for the station making the request
$stationName = 'Your Station Name';        // Name or description of the station
$timeout = 10;                             // Timeout in seconds for server response
$json = false;                             // Set to true if you want the API to return responses as JSON
```

## Usage Examples

### Slovak Companies API

```php
<?php
require_once(__DIR__ . '/../FinStatApi/FinstatApi.php');
require_once(__DIR__ . '/../FinStat.Client/ViewModel/AutoCompleteResult.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Detail/BaseResult.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Detail/BasicResult.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Detail/DetailResult.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Detail/ExtendedResult.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Detail/UltimateResult.php');

// Initialize the API client
$api = new FinstatApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

// Get basic company information
$ico = '35757442'; // Example company ICO
$basicInfo = $api->Request($ico, "basic", $json);

// Get detailed company information
$detailInfo = $api->Request($ico, "detail", $json);

// Get extended company information 
$extendedInfo = $api->Request($ico, "extended", $json);

// Get ultimate (most comprehensive) company information
$ultimateInfo = $api->Request($ico, "ultimate", $json);

// Search for companies by name
$autocompleteResults = $api->RequestAutoComplete('volkswagen', $json);

// Check API usage limits
$limits = $api->GetAPILimits();
```

### Czech Companies API

```php
<?php
require_once(__DIR__ . '/../FinStatApiCZ/FinstatApi.php');
require_once(__DIR__ . '/../FinStat.Client/ViewModel/AutoCompleteResult.php');
require_once(__DIR__ . '/../FinStatCZ.ViewModel/Detail/DetailResult.php');
require_once(__DIR__ . '/../FinStatCZ.ViewModel/Detail/PremiumCZResult.php');

// Initialize the API client for Czech companies
$apiUrl = 'https://cz.finstat.sk/api/';
$api = new FinstatApiCz\FinstatApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

// Get basic company information
$ico = '48207349'; // Example Czech company ICO
$basicInfo = $api->Request($ico, "basic", $json);

// Get detailed company information
$detailInfo = $api->Request($ico, "detail", $json);

// Get premium company information
$premiumInfo = $api->Request($ico, "premiumcz", $json);

// Search for companies by name
$autocompleteResults = $api->RequestAutoComplete('volkswagen', $json);
```

### Monitoring API

```php
<?php
require_once(__DIR__ . '/../FinStatApi/FinstatMonitoringApi.php');
require_once(__DIR__ . '/../FinStat.Client/ViewModel/Monitoring/MonitoringReportResult.php');

// Initialize the monitoring API client
$api = new FinstatMonitoringApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

// Add a company to monitoring by ICO
$ico = '35757442';
$success = $api->AddToMonitoring($ico, $json);

// Add a date to monitoring
$date = "1.1.1991";
$success = $api->AddDateToMonitoring($date, $json);

// Get list of monitored ICOs
$monitoredList = $api->MonitoringList($json);

// Get list of monitored dates
$monitoredDateList = $api->MonitoringDateList($json);

// Remove a company from monitoring
$success = $api->RemoveFromMonitoring($ico, $json);

// Remove a date from monitoring
$success = $api->RemoveDateFromMonitoring($date, $json);

// Get monitoring reports
$reports = $api->MonitoringReport($json);
$dateReports = $api->MonitoringDateReport($json);
```

### Daily Diff API

```php
<?php
require_once(__DIR__ . '/../FinStatApi/FinstatDailyDiffApi.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Diff/DailyDiff.php');
require_once(__DIR__ . '/../FinStat.ViewModel/Diff/DailyDiffList.php');

// Initialize the daily diff API client
$api = new FinstatDailyDiffApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

// Get list of available daily diffs
$list = $api->RequestListOfDailyDiffs($json);

// Download a specific daily diff file
$file = "example_file.zip";
$data = $api->DownloadDailyDiffFile($file, $file);
```

## Response Data

The API returns structured data objects containing company information. Depending on the request type, different data fields are available:

### Basic Fields (available in all responses)
- `Ico` - Company ID
- `Name` - Company name
- `Street` - Street address
- `StreetNumber` - Street number
- `ZipCode` - ZIP/Postal code
- `City` - City
- `District` - District
- `Region` - Region
- `Country` - Country
- `Url` - Finstat URL for the company

### Additional Fields (available in detail/extended/ultimate responses)
- Legal form information
- Registration details
- Financial data (revenue, profit)
- Credit scores
- Warning indicators
- Bank accounts
- Employee counts
- Ownership information
- Statutory representatives
- Web pages
- And many more...

## Error Handling

The API client throws exceptions in case of errors. Make sure to handle these appropriately:

```php
try {
    $response = $api->Request($ico, "basic", $json);
} catch (Exception $e) {
    // Handle error
    $code = $e->getCode();
    $message = $e->getMessage();
    $data = $e->getData();
}
```

## API Limits

You can check your current API usage limits:

```php
$limits = $api->GetAPILimits();

// Example output format:
// [
//   'daily' => ['current' => 10, 'max' => 100],
//   'monthly' => ['current' => 50, 'max' => 1000]
// ]
```

## Requirements

- PHP 7.1 or higher
- cURL extension
- Zlib extension
- DOM extension
- JSON extension (for JSON responses)
- SimpleXML extension (for XML responses)

## License

MIT License

## Support

For API access and keys, please contact info@finstat.sk