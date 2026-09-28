<?php

namespace FinStat\Client\Exceptions;

/**
 * Exception thrown when the server returns HTTP 502 Bad Gateway.
 *
 * An upstream register that FinStat queries on your behalf was temporarily
 * unavailable or the query to it failed. Currently emitted by the distraint
 * endpoints that query CRE (Centrálny register exekúcií) live —
 * `distraintSearch` and `distraintDetail`.
 *
 * **No credit is charged for a request that fails this way**, so the request
 * is safe to retry once the register is back. Mirrors
 * `FinstatApiException.FailTypeEnum.RegisterUnavailable` in the C# client.
 *
 * @package FinStat\Client\Exceptions
 */
class RegisterUnavailableException extends FinstatException
{
    /**
     * Create a new RegisterUnavailableException
     *
     * @param string $message The error message
     * @param int $code The error code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(
        string $message = "Upstream register is temporarily unavailable, no credit was charged.",
        int $code = 502,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Whether the failed request may be retried.
     *
     * Always true for this exception — the upstream register was unreachable
     * and no credit was consumed.
     *
     * @return bool
     */
    public function isRetryable(): bool
    {
        return true;
    }
}
