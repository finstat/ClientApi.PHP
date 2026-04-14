<?php

namespace FinStat\Client;

/**
 * Simple HTTP Response class
 * Mimics the Requests library response structure using native PHP
 *
 * @package FinStat\Client
 */
class HttpResponse
{
    /**
     * @var int HTTP status code
     */
    public $status_code;

    /**
     * @var string Response body
     */
    public $body;

    /**
     * @var bool Whether the request was successful (2xx status)
     */
    public $success;

    /**
     * @var HttpHeaders Response headers
     */
    public $headers;

    /**
     * Constructor
     *
     * @param int $status_code HTTP status code
     * @param string $body Response body
     * @param array $headers Response headers as array
     */
    public function __construct(int $status_code, string $body, array $headers)
    {
        $this->status_code = $status_code;
        $this->body = $body;
        $this->success = $status_code >= 200 && $status_code < 300;
        $this->headers = new HttpHeaders($headers);
    }
}
