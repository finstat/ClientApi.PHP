# Finstat API Client for PHP

This repository contains a PHP client for interacting with the Finstat API services. Finstat provides financial and corporate data for businesses in Slovakia and the Czech Republic.

## Features

- Retrieve detailed company information by ICO (Company ID)
- Search companies using autocomplete functionality
- Monitor changes in company data, and the proceedings of monitored companies
- Financial statements and their template legends
- Access daily data diffs and pre-generated reports
- Bankruptcy, restructuring and distraint (CRE) searches
- Support for both Slovak and Czech business registries

Every class is loaded by Composer's autoloader (`vendor/autoload.php`); the examples below assume it is included.

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
require_once __DIR__ . '/vendor/autoload.php';

// Initialize the API client
$api = new FinStat\Api\FinstatApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

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
require_once __DIR__ . '/vendor/autoload.php';

// Initialize the API client for Czech companies
$apiUrl = 'https://cz.finstat.sk/api/';
$api = new FinStatCZ\FinstatApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

// Get basic company information
$ico = '48207349'; // Example Czech company ICO
$basicInfo = $api->Request($ico, "basic", $json);

// Get detailed company information
$detailInfo = $api->Request($ico, "detail", $json);

// Get premium company information (financial data, bank accounts, turnover category)
$premiumInfo = $api->RequestPremiumCZ($ico, $json);

// Get elite company information: contacts, other CZ NACE activities, office count,
// vehicle fleet, ISIR insolvency proceedings, financial indicators (2 years)
$eliteInfo = $api->RequestEliteCZ($ico, $json);

// Get ultimate company information: everything from elite plus OR ČR / RŽP persons,
// statutory and procuration action, business subjects / trade licences, office list,
// vehicle fleet breakdowns, ISIR creditors and claim applications, credit scores and
// financial indicators (4 years), insolvency claims filed by the company as creditor
$ultimateInfo = $api->RequestUltimateCZ($ico, $json);

// Language of response texts (code list titles, NACE, ...): 'cs' or 'sk', passed per request
// as the last parameter of every Request* method; honoured by the Czech API only.
// Without it the server uses its default (Czech) or the Accept-Language header.
$eliteInfoSk = $api->RequestEliteCZ($ico, $json, 'sk');

// Search for companies by name
$autocompleteResults = $api->RequestAutoComplete('volkswagen', $json);
```

### Monitoring API

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

// Initialize the monitoring API client (works with the Czech API URL too)
$api = new FinStat\Api\FinstatMonitoringApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

// Every call takes an optional monitoring category before $json; null uses the default one
$category = null;

// Add a company to monitoring by ICO
$ico = '35757442';
$success = $api->AddToMonitoring($ico, $category, $json);

// Add a date to monitoring
$date = "1.1.1991";
$success = $api->AddDateToMonitoring($date, $category, $json);

// Get list of monitored ICOs
$monitoredList = $api->MonitoringList($category, $json);

// Get list of monitored dates
$monitoredDateList = $api->MonitoringDateList($category, $json);

// Remove a company from monitoring
$success = $api->RemoveFromMonitoring($ico, $category, $json);

// Remove a date from monitoring
$success = $api->RemoveDateFromMonitoring($date, $category, $json);

// Get monitoring reports
$reports = $api->MonitoringReport($category, $json);
$dateReports = $api->MonitoringDateReport($category, $json);

// Bankruptcy and restructuring proceedings of the monitored companies and dates (Slovak API)
$proceedings = $api->MonitoringProceedings($json);
$dateProceedings = $api->MonitoringDateProceedings($json);

// Monitoring categories of the account (Czech API, or a Premium license on the Slovak one)
$categories = $api->MonitoringCategories($json);
```

### Statement API

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$api = new FinStat\Api\FinstatStatementApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

// Statements of a company, each with the templates it can be shown in
$statements = $api->RequestStatements($ico, $json);

// One statement in one template: Template2011v2, Template2014, Template2014micro, TemplateFinancial,
// TemplateROPO or TemplateNujPU (the last two are non-profit: Expenses and Revenue instead of IncomeStatement)
$statement = $api->RequestStatementDetail($ico, 2025, 'Template2014', $json);

// Row names of a template, in the given language (default 'sk')
$legend = $api->RequestStatementLegend('Template2014', 'sk', $json);
```

In XML mode templates are names. JSON sends them as the server's numbers: 1 Template2011v2,
2 Template2014, 3 Template2014micro, 4 TemplateFinancial, 8 TemplateROPO, 9 TemplateNujPU.

### Daily Diff API

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

// Initialize the daily diff API client
$api = new FinStat\Api\FinstatDailyDiffApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

// Get list of available daily diffs
$list = $api->RequestListOfDailyDiffs($json);

// Download a specific daily diff file
$file = "example_file.zip";
$data = $api->DownloadDailyDiffFile($file, $file);

// The same for statements, statements in the 2014 template and Ultimate data:
// FinstatDailyStatementDiffApi, FinstatDailyStatement2014DiffApi (each with RequestStatementLegend)
// and FinstatDailyUltimateDiffApi
```

### Reporting API

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$api = new FinStat\Api\FinstatReportingApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

// Report topics, the reports of one topic, and one report file
$topics = $api->RequestTopics($json);
$reports = $api->RequestList($topics[0]->ID, $json);
$data = $api->DownloadReportFile($reports[0]->FileName, 'report.xlsx');
```

### Bankruptcy and Restructuring API

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

// Initialize the bankruptcy and restructuring API client
$api = new FinStat\Api\FinstatBankruptcyRestructuringApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

// Get list of person bankruptcy proceedings ($dateOfBirth is a DateTime)
$list = $api->RequestPersonBankruptcyProceedings($name, $surname, new DateTime('1975-03-12'), $json);

// Get list of company bankruptcy and restructuring proceedings by ico
$list = $api->RequestCompanyBankruptcyRestructuring($ico, null, $json);

// Get list of company bankruptcy and restructuring proceedings by name
$list = $api->RequestCompanyBankruptcyRestructuring(null, $name, $json);

```

### Distraints API (Centrálny register exekúcií)

```php
// This client is namespaced and loaded through composer's PSR-4 autoloader
require_once(__DIR__ . '/vendor/autoload.php');

use FinStat\Api\FinstatDistraintApi;

// Initialize the distraint API client
$api = new FinstatDistraintApi($apiUrl, $apiKey, $privateKey, $stationId, $stationName, $timeout);

// Live search — CHARGED. At least one of ico / companyName / fileReference /
// surname is required, and surname alone additionally needs dateOfBirth or city.
// dateOfBirth must be `d.m.Y`; any other format is rejected with HTTP 400.
$result = $api->RequestDistraintSearch(null, 'Mrkvicka', '12.03.1975', null, null, null, $json);

// Expand details — CHARGED PER UNIQUE ID, not per call. Max 200 ids.
// Returns a DistraintDetailResults wrapper holding a DistraintDetails list.
$token = $result->Distraints[0]->DetailToken;
$ids = array_map(function ($d) { return $d->DetailId; }, $result->Distraints);
$details = $api->RequestDistraintDetail($token, $ids, $json);
foreach ($details->DistraintDetails as $detail) {
    echo $detail->Code . ' ' . $detail->SumOutstanding . ' ' . $detail->Currency;
}

// Re-read what a previous live call already paid for — these three cost no credit
$stored     = $api->RequestDistraintResults(null, 'Mrkvicka', '12.03.1975', null, null, null, $json);
$byToken    = $api->RequestDistraintResultsByToken($token, $json);
$oneDetail  = $api->RequestDistraintStoredDetail($result->Distraints[0]->StoredDetailId, $json);
```

Only `RequestDistraintSearch` and `RequestDistraintDetail` spend credit; the three
"stored" calls re-read data a previous live call already paid for. Every call still
counts against the daily/monthly API call allowance.

When the CRE register itself is unreachable the two live calls throw
`RegisterUnavailableException` (HTTP 502) — **no credit is charged**, so the request
is safe to retry.

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
use FinStat\Client\Exceptions\FinstatException;
use FinStat\Client\Exceptions\LimitReachedException;
use FinStat\Client\Exceptions\NotFoundException;
use FinStat\Client\Exceptions\RegisterUnavailableException;

try {
    $response = $api->Request($ico, "basic", $json);
} catch (NotFoundException $e) {
    // HTTP 404 - no company for this ICO
} catch (LimitReachedException $e) {
    // HTTP 402 - daily/monthly allowance spent, or not enough credit
    $daily = $e->getDailyCurrent() . '/' . $e->getDailyMax();
} catch (RegisterUnavailableException $e) {
    // HTTP 502 - upstream register down; no credit was charged, safe to retry
} catch (FinstatException $e) {
    // Every exception above extends FinstatException, so this catches the rest
    $code    = $e->getCode();        // the HTTP status code
    $message = $e->getMessage();     // the server response body, when it sent one
    $url     = $e->getRequestUrl();  // request context, set on every exception
    $param   = $e->getRequestParameter();
}
```

All exceptions extend `FinstatException`, so a single `catch (FinstatException $e)`
is enough if you do not need to tell the cases apart. The specific types are
`NotFoundException` (404), `LimitReachedException` (402), `UnauthorizedException` (401),
`AuthenticationException` / `InsufficientAccessException` / `LicenseExpiredException` /
`AccessDisabledException` / `InvalidHashException` (403), `BadRequestException` (400),
`RateLimitExceededException` (429), `GdprRestrictionException` (451),
`RegisterUnavailableException` (502) and `ParseException`.

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