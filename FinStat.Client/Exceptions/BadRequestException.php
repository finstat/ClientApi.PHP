<?php

namespace FinStat\Client\Exceptions;

/**
 * Exception thrown when a bad request is made (HTTP 400)
 * 
 * @package FinStat\Client\Exceptions
 */
class BadRequestException extends FinstatException
{
    /**
     * Create a new BadRequestException
     * 
     * @param string $message The error message
     * @param int $code The error code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(
        string $message = "Invalid request parameters.",
        int $code = 400,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
