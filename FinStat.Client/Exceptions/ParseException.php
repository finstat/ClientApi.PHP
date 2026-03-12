<?php

namespace FinStat\Client\Exceptions;

/**
 * Exception thrown when XML or JSON parsing fails
 * 
 * @package FinStat\Client\Exceptions
 */
class ParseException extends FinstatException
{
    /**
     * Create a new ParseException
     * 
     * @param string $message The error message
     * @param int $code The error code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(
        string $message = "Error parsing response data.",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
