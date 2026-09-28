<?php

namespace FinStat\Client;

/**
 * Simple HTTP Client using native PHP cURL with file_get_contents fallback
 * Drop-in replacement for Requests library
 *
 * @package FinStat\Client
 */
class HttpClient
{
    /**
     * Make a POST request using native cURL or file_get_contents fallback
     *
     * @param string $url Request URL
     * @param array|null $headers Custom headers as "Name: value" strings or name => value pairs
     * @param array $data POST data
     * @param array $options Request options (timeout, etc.)
     * @return HttpResponse Response object
     * @throws \RuntimeException On HTTP error
     */
    public static function post(string $url, ?array $headers, array $data, array $options): HttpResponse
    {
        // Check if cURL is available
        if (function_exists('curl_init') && function_exists('curl_exec')) {
            return self::postWithCurl($url, $headers, $data, $options);
        }

        // Fallback to file_get_contents with stream context
        if (ini_get('allow_url_fopen')) {
            return self::postWithFileGetContents($url, $headers, $data, $options);
        }

        // No HTTP client available
        throw new \RuntimeException(
            'No HTTP client available. Please enable either cURL extension or allow_url_fopen in php.ini'
        );
    }

    /**
     * Make POST request using cURL
     *
     * @param string $url Request URL
     * @param array|null $headers Custom headers
     * @param array $data POST data
     * @param array $options Request options
     * @return HttpResponse Response object
     * @throws \RuntimeException On cURL error
     */
    private static function postWithCurl(string $url, ?array $headers, array $data, array $options): HttpResponse
    {
        // Initialize cURL
        $ch = curl_init($url);
        if ($ch === false) {
            throw new \RuntimeException('Failed to initialize cURL');
        }

        // Set cURL options
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $options['follow_redirects'] ?? false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout'] ?? 10);

        // Custom headers, if any. Field names go out lowercase: HTTP/2 and HTTP/3
        // require it (RFC 9113 8.2.1), and HTTP/1.1 treats them case-insensitively,
        // so lowercase is the one spelling valid on every version.
        $curlHeaders = self::normalizeHeaders($headers);
        if ($curlHeaders !== []) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        }

        // SSL verification (disable for localhost, enable for production)
        if (strpos($url, 'localhost') === false && strpos($url, '127.0.0.1') === false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        // Capture response headers
        $responseHeaders = [];
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$responseHeaders) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) {
                return $len;
            }
            $responseHeaders[trim($header[0])] = trim($header[1]);
            return $len;
        });

        // Execute request
        $body = curl_exec($ch);

        // Check for cURL errors
        if ($body === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            throw new \RuntimeException("cURL error ({$errno}): {$error}");
        }

        // Get status code
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // No curl_close() here: it is a no-op since PHP 8.0 and deprecated since
        // PHP 8.5. The handle is a function local, so it is released as soon as
        // this method returns (or unwinds) on every supported PHP version.

        // Return response object
        return new HttpResponse($statusCode, $body, $responseHeaders);
    }

    /**
     * Make POST request using file_get_contents with stream context
     * Fallback method when cURL is not available
     *
     * @param string $url Request URL
     * @param array|null $headers Custom headers
     * @param array $data POST data
     * @param array $options Request options
     * @return HttpResponse Response object
     * @throws \RuntimeException On HTTP error
     */
    private static function postWithFileGetContents(string $url, ?array $headers, array $data, array $options): HttpResponse
    {
        // Prepare POST data
        $postData = http_build_query($data);

        // Prepare headers. Lowercase field names — see normalizeHeaders().
        $httpHeaders = array_merge([
            'content-type: application/x-www-form-urlencoded',
            'content-length: ' . strlen($postData)
        ], self::normalizeHeaders($headers));

        // SSL verification (disable for localhost, enable for production)
        $sslVerify = true;
        if (strpos($url, 'localhost') !== false || strpos($url, '127.0.0.1') !== false) {
            $sslVerify = false;
        }

        // Create stream context
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $httpHeaders),
                'content' => $postData,
                'timeout' => $options['timeout'] ?? 10,
                'follow_location' => $options['follow_redirects'] ?? false,
                'ignore_errors' => true, // Get content even on error status
            ],
            'ssl' => [
                'verify_peer' => $sslVerify,
                'verify_peer_name' => $sslVerify,
            ]
        ]);

        // Pre-initialize the locally scoped variable the http:// stream wrapper
        // fills in after the request. Two reasons:
        //  - the wrapper only populates it when the calling function already
        //    declares it, and
        //  - PHP 8.5 deprecates *reading* the predefined $http_response_header,
        //    but only in a function that never assigns to it, so assigning here
        //    keeps PHP 8.5 quiet. The replacement, http_get_last_response_headers(),
        //    exists only since PHP 8.4, hence the fallback below.
        $http_response_header = null;

        // Make request
        $body = @file_get_contents($url, false, $context);

        if ($body === false) {
            $error = error_get_last();
            throw new \RuntimeException('HTTP request failed: ' . ($error['message'] ?? 'Unknown error'));
        }

        // Prefer the PHP 8.4+ API, fall back to the variable it replaces
        $rawHeaders = function_exists('http_get_last_response_headers')
            ? http_get_last_response_headers()
            : $http_response_header;
        if (!is_array($rawHeaders)) {
            $rawHeaders = [];
        }

        // Parse response headers
        $responseHeaders = [];
        foreach ($rawHeaders as $header) {
            $parts = explode(':', $header, 2);
            if (count($parts) === 2) {
                $responseHeaders[trim($parts[0])] = trim($parts[1]);
            }
        }

        // Extract status code from first header line
        $statusCode = 500; // Default to server error
        if (isset($rawHeaders[0])) {
            if (preg_match('/HTTP\/\d(?:\.\d)?\s+(\d+)/', $rawHeaders[0], $matches)) {
                $statusCode = (int)$matches[1];
            }
        }

        return new HttpResponse($statusCode, $body, $responseHeaders);
    }

    /**
     * Normalize custom headers to a list of "name: value" strings with
     * lowercase field names.
     *
     * HTTP/2 and HTTP/3 carry field names in lowercase only — an uppercase
     * name is a malformed request that the peer may reset (RFC 9113 8.2.1).
     * HTTP/1.1 compares names case-insensitively, so lowercasing is safe
     * across every protocol version. Values are left untouched.
     *
     * Accepts either ["Name: value", ...] or ["Name" => "value", ...].
     *
     * @param array|null $headers Custom headers
     * @return array List of "name: value" strings
     */
    private static function normalizeHeaders(?array $headers): array
    {
        if (empty($headers)) {
            return [];
        }

        $normalized = [];
        foreach ($headers as $name => $value) {
            if (is_int($name)) {
                // "Name: value" form — split on the first colon only
                $parts = explode(':', (string)$value, 2);
                if (count($parts) < 2) {
                    continue;
                }
                $name = $parts[0];
                $value = $parts[1];
            }

            $name = strtolower(trim((string)$name));
            if ($name === '') {
                continue;
            }

            $normalized[] = $name . ': ' . trim((string)$value);
        }

        return $normalized;
    }
}
