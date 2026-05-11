<?php

namespace FinStat\Client\Exceptions;

/**
 * Exception thrown when the server returns HTTP 403 with a message indicating
 * that the caller's FinStat licence has expired.
 *
 * Mirrors `FinstatApiException.FailTypeEnum.LicenseExpired` in the C# client.
 * Subclass of {@see AuthenticationException} so existing catch blocks keep
 * working for callers that don't yet discriminate between 403 sub-types.
 *
 * @package FinStat\Client\Exceptions
 */
class LicenseExpiredException extends AuthenticationException
{
    public function __construct(
        string $message = "Your API access and FinStat license expired.",
        int $code = 403,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
