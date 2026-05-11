<?php

namespace FinStat\Client\Exceptions;

/**
 * Exception thrown when the server returns HTTP 403 with a message indicating
 * that the caller's licence is valid but does not cover the requested endpoint
 * (e.g. asking for Ultimate when only Basic is enabled).
 *
 * Mirrors `FinstatApiException.FailTypeEnum.InsufficientAccess` in the C#
 * client. Subclass of {@see AuthenticationException} for backwards compatibility.
 *
 * @package FinStat\Client\Exceptions
 */
class InsufficientAccessException extends AuthenticationException
{
    public function __construct(
        string $message = "Insufficient access for this endpoint.",
        int $code = 403,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
