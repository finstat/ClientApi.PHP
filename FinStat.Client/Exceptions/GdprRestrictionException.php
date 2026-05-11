<?php

namespace FinStat\Client\Exceptions;

/**
 * Exception thrown when the server returns HTTP 451 Unavailable For Legal Reasons.
 *
 * The requested company record is GDPR-anonymized and the caller's licence does
 * not allow access to anonymized subjects. Mirrors
 * `FinstatApiException.FailTypeEnum.GdprRestriction` in the C# client.
 *
 * @package FinStat\Client\Exceptions
 */
class GdprRestrictionException extends FinstatException
{
    /**
     * Create a new GdprRestrictionException
     *
     * @param string $message The error message
     * @param int $code The error code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(
        string $message = "Limited access due to GDPR restrictions.",
        int $code = 451,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
