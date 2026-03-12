<?php

namespace FinStat\Client\Exceptions;

/**
 * Exception thrown when API rate limits are exceeded (HTTP 402)
 * 
 * @package FinStat\Client\Exceptions
 */
class LimitReachedException extends FinstatException
{
    /**
     * @var int Current daily API call count
     */
    protected $dailyCurrent;
    
    /**
     * @var int Maximum daily API call limit
     */
    protected $dailyMax;
    
    /**
     * @var int Current monthly API call count
     */
    protected $monthlyCurrent;
    
    /**
     * @var int Maximum monthly API call limit
     */
    protected $monthlyMax;

    /**
     * Create a new LimitReachedException
     * 
     * @param int $dailyCurrent Current daily usage
     * @param int $dailyMax Daily limit
     * @param int $monthlyCurrent Current monthly usage
     * @param int $monthlyMax Monthly limit
     * @param int $code The error code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(
        int $dailyCurrent,
        int $dailyMax,
        int $monthlyCurrent,
        int $monthlyMax,
        int $code = 402,
        ?\Throwable $previous = null
    ) {
        $this->dailyCurrent = $dailyCurrent;
        $this->dailyMax = $dailyMax;
        $this->monthlyCurrent = $monthlyCurrent;
        $this->monthlyMax = $monthlyMax;

        $message = "API limit reached. Daily: {$dailyCurrent}/{$dailyMax}, Monthly: {$monthlyCurrent}/{$monthlyMax}";
        
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get current daily usage
     * 
     * @return int
     */
    public function getDailyCurrent(): int
    {
        return $this->dailyCurrent;
    }

    /**
     * Get daily limit
     * 
     * @return int
     */
    public function getDailyMax(): int
    {
        return $this->dailyMax;
    }

    /**
     * Get current monthly usage
     * 
     * @return int
     */
    public function getMonthlyCurrent(): int
    {
        return $this->monthlyCurrent;
    }

    /**
     * Get monthly limit
     * 
     * @return int
     */
    public function getMonthlyMax(): int
    {
        return $this->monthlyMax;
    }

    /**
     * Check if daily limit is exceeded
     * 
     * @return bool
     */
    public function isDailyLimitExceeded(): bool
    {
        return $this->dailyCurrent >= $this->dailyMax;
    }

    /**
     * Check if monthly limit is exceeded
     * 
     * @return bool
     */
    public function isMonthlyLimitExceeded(): bool
    {
        return $this->monthlyCurrent >= $this->monthlyMax;
    }
}
