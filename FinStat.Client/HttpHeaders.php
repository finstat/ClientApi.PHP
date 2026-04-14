<?php

namespace FinStat\Client;

/**
 * HTTP Headers class
 * Provides ArrayAccess interface for headers
 *
 * @package FinStat\Client
 */
class HttpHeaders
{
    /**
     * @var array Header storage
     */
    private $headers = [];

    /**
     * Constructor
     *
     * @param array $headers Headers array
     */
    public function __construct(array $headers)
    {
        // Normalize header names to lowercase for case-insensitive access
        foreach ($headers as $name => $value) {
            $this->headers[strtolower($name)] = $value;
        }
    }

    /**
     * Check if header exists
     *
     * @param string $name Header name
     * @return bool
     */
    public function offsetExists($name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * Get header value
     *
     * @param string $name Header name
     * @return string|null
     */
    public function offsetGet($name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }
}
