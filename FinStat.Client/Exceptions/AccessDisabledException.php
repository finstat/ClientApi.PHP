<?php

namespace FinStat\Client\Exceptions;

/**
 * Exception thrown when the server returns HTTP 403 with a message indicating
 * that the caller's API access is administratively disabled (the key is known
 * but switched off).
 *
 * Mirrors `FinstatApiException.FailTypeEnum.AccessDisabled` in the C# client.
 * Subclass of {@see AuthenticationException} for backwards compatibility.
 *
 * @package FinStat\Client\Exceptions
 */
class AccessDisabledException extends AuthenticationException
{
    public function __construct(
        string $message = "Your API access is disabled.",
        int $code = 403,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
