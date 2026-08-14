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
     * @param array|null $headers Custom headers (not used in current implementation)
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

        // Prepare headers
        $httpHeaders = [
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($postData)
        ];

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

        // Make request
        $body = @file_get_contents($url, false, $context);

        if ($body === false) {
            $error = error_get_last();
            throw new \RuntimeException('HTTP request failed: ' . ($error['message'] ?? 'Unknown error'));
        }

        // Parse response headers
        $responseHeaders = [];
        if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                $parts = explode(':', $header, 2);
                if (count($parts) === 2) {
                    $responseHeaders[trim($parts[0])] = trim($parts[1]);
                }
            }
        }

        // Extract status code from first header line
        $statusCode = 500; // Default to server error
        if (isset($http_response_header[0])) {
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $matches)) {
                $statusCode = (int)$matches[1];
            }
        }

        return new HttpResponse($statusCode, $body, $responseHeaders);
    }
}
