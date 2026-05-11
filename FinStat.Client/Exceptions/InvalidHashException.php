<?php

namespace FinStat\Client\Exceptions;

/**
 * Exception thrown when the server returns HTTP 403 with a message indicating
 * that the verification hash did not match — usually a wrong privateKey, a
 * mismatched parameter, or an incorrect hashing routine on the client.
 *
 * Mirrors `FinstatApiException.FailTypeEnum.InvalidHash` in the C# client.
 * Subclass of {@see AuthenticationException} for backwards compatibility.
 *
 * @package FinStat\Client\Exceptions
 */
class InvalidHashException extends AuthenticationException
{
    public function __construct(
        string $message = "Invalid verification hash. Check privateKey, parameter or the hash computation method.",
        int $code = 403,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
