<?php

namespace FinStat\Client\Exceptions;

/**
 * Exception thrown when authentication fails (HTTP 403)
 * 
 * @package FinStat\Client\Exceptions
 */
class AuthenticationException extends FinstatException
{
    /**
     * Create a new AuthenticationException
     * 
     * @param string $message The error message
     * @param int $code The error code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(
        string $message = "Authentication failed. Check API credentials.",
        int $code = 403,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
