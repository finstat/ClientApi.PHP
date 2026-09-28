<?php

namespace FinStat\Client\Exceptions;

/**
 * Exception thrown when the server returns HTTP 429 Too Many Requests.
 *
 * The per-second request quota was exceeded. This is distinct from
 * `LimitReachedException` (HTTP 402), which covers the daily/monthly call
 * allowance and — on the distraint endpoints — insufficient credit.
 *
 * Throttling is transient, so the request may be retried after a short pause.
 * Mirrors `FinstatApiException.FailTypeEnum.RateLimitExceeded` in the C# client.
 *
 * @package FinStat\Client\Exceptions
 */
class RateLimitExceededException extends FinstatException
{
    /**
     * Create a new RateLimitExceededException
     *
     * @param string $message The error message
     * @param int $code The error code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(
        string $message = "Request per second quota exceeded.",
        int $code = 429,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Whether the failed request may be retried.
     *
     * Always true for this exception — throttling is transient.
     *
     * @return bool
     */
    public function isRetryable(): bool
    {
        return true;
    }
}
