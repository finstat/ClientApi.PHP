<?php

namespace FinStat\Client\Exceptions;

/**
 * Exception thrown when the server returns HTTP 401 Unauthorized.
 *
 * Matches the SK API "Reporting" endpoint behaviour where the caller's licence
 * does not cover editing a particular monitoring topic. Distinct from 403
 * (`AuthenticationException`) which signals an invalid customer key or denied
 * API access. Mirrors `FinstatApiException.FailTypeEnum.Unauthorized` in the
 * C# client.
 *
 * @package FinStat\Client\Exceptions
 */
class UnauthorizedException extends FinstatException
{
    /**
     * Create a new UnauthorizedException
     *
     * @param string $message The error message
     * @param int $code The error code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(
        string $message = "Unauthorized. The current licence does not permit this operation.",
        int $code = 401,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
