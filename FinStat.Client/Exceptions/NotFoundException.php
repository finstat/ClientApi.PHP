<?php

namespace FinStat\Client\Exceptions;

/**
 * Exception thrown when a requested resource is not found (HTTP 404)
 * 
 * @package FinStat\Client\Exceptions
 */
class NotFoundException extends FinstatException
{
    /**
     * Create a new NotFoundException
     * 
     * @param string|null $parameter The parameter (ICO, etc.) that was not found
     * @param int $code The error code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(?string $parameter = null, int $code = 404, ?\Throwable $previous = null)
    {
        $message = $parameter 
            ? "Resource not found for parameter: {$parameter}"
            : "Resource not found";
        
        parent::__construct($message, $code, $previous);
        $this->requestParameter = $parameter;
    }
}
