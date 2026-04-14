# VS Code Launch Configurations

## Overview

The `.vscode/launch.json` file contains debugging configurations for the FinStat PHP API Client project. Each example configuration starts PHP's built-in web server and automatically opens the result in your default browser.

---

## Prerequisites

### 1. PHP Debug Extension

1. Open VS Code Extensions (Ctrl+Shift+X)
2. Search for "PHP Debug"
3. Install `xdebug.php-debug`

### 2. XDebug (optional, for breakpoints)

Without XDebug the examples still run and open in the browser, but breakpoints will not work.

**Windows** - add to `php.ini`:
```ini
zend_extension=xdebug
xdebug.mode=debug
xdebug.start_with_request=trigger
xdebug.client_port=9003
xdebug.client_host=127.0.0.1
```

**Linux/Mac:**
```bash
sudo apt-get install php-xdebug  # Ubuntu/Debian
brew install php-xdebug          # macOS
```

Verify: `php -v` should show `with Xdebug v3.x`.

### 3. SSL CA Bundle

PHP needs a CA certificate bundle for HTTPS API calls. If you see `cURL error (60): SSL certificate problem`, run as admin in PowerShell:

```powershell
New-Item -ItemType Directory -Force -Path "C:\Program Files\PHP\8.4.6\extras\ssl"
Invoke-WebRequest -Uri "https://curl.se/ca/cacert-2024-07-02.pem" -OutFile "C:\Program Files\PHP\8.4.6\extras\ssl\cacert.pem"
(Get-Content "C:\Program Files\PHP\8.4.6\php.ini") `
  -replace '^;curl\.cainfo =','curl.cainfo = "C:\Program Files\PHP\8.4.6\extras\ssl\cacert.pem"' `
  -replace '^;openssl\.cafile=','openssl.cafile = "C:\Program Files\PHP\8.4.6\extras\ssl\cacert.pem"' |
  Set-Content "C:\Program Files\PHP\8.4.6\php.ini"
```

---

## Available Configurations

### 1. Listen for XDebug

Listens for incoming XDebug connections from a web server or external process. Use this when debugging requests from a browser hitting Apache/nginx.

- Port: 9003

### 2. Launch currently open script

Runs whichever PHP file is active in the editor. Output appears in the Debug Console.

### 3. Debug: FinStat API Example (Main)

Starts a PHP built-in server on **port 8081** serving `FinStat/Example.php`, then opens the browser.

- **File:** `FinStat/Example.php`
- **URL:** `http://localhost:8081/Example.php`
- **Tests:** Basic, Detail, Extended, Ultimate company data and AutoComplete search

### 4. Debug: FinStat CZ API Example

Starts a PHP built-in server on **port 8082** serving `FinStatCZ/Example.php`.

- **File:** `FinStatCZ/Example.php`
- **URL:** `http://localhost:8082/Example.php`
- **Tests:** Czech company basic, detail, premium, and elite data

### 5. Debug: Monitoring API Example

Starts a PHP built-in server on **port 8083** serving `FinStatMonitoring/Example.php`.

- **File:** `FinStatMonitoring/Example.php`
- **URL:** `http://localhost:8083/Example.php`
- **Tests:** Add/remove companies from watchlist, monitoring reports

### 6. Debug: Reporting API Example

Starts a PHP built-in server on **port 8084** serving `FinStatReporting/Example.php`.

- **File:** `FinStatReporting/Example.php`
- **URL:** `http://localhost:8084/Example.php`
- **Tests:** Generate and download PDF/Excel reports

### 7. Debug: Daily Diff Example

Starts a PHP built-in server on **port 8085** serving `FinStatDailyDiff/Example.php`.

- **File:** `FinStatDailyDiff/Example.php`
- **URL:** `http://localhost:8085/Example.php`
- **Tests:** List and download daily difference files

### 8. Debug: Bankruptcy Example

Starts a PHP built-in server on **port 8086** serving `FinstatBankruptcyRestructuring/Example.php`.

- **File:** `FinstatBankruptcyRestructuring/Example.php`
- **URL:** `http://localhost:8086/Example.php`
- **Tests:** Search bankruptcy and restructuring proceedings

---

## How to Use

1. Select a configuration from the Debug dropdown (top of the Debug panel)
2. Press **F5** (or click the green play button)
3. The PHP built-in server starts and your browser opens the example page automatically
4. Set breakpoints in the code (requires XDebug)
5. Press the **Stop** button in VS Code to shut down the server when done

Each example runs on its own port, so multiple examples can run simultaneously without conflict.

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Breakpoints not hit | Install XDebug and verify with `php -v` |
| Browser doesn't open | Check that no other process is using the port (e.g. `netstat -ano \| findstr 8081`) |
| SSL certificate error | Follow the CA bundle setup above |
| Port already in use | Stop the previous debug session first, or change the port in `launch.json` |
| "Unknown configuration attribute" | Update the PHP Debug extension to latest version |
