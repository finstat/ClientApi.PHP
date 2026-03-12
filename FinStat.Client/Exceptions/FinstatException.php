<?php

namespace FinStat\Client\Exceptions;

use Exception;

/**
 * Base exception class for all FinStat API exceptions
 * 
 * @package FinStat\Client\Exceptions
 */
class FinstatException extends Exception
{
    /**
     * @var string|null The URL that was requested when the exception occurred
     */
    protected $requestUrl;
    
    /**
     * @var mixed The parameter that was passed to the request
     */
    protected $requestParameter;

    /**
     * Set the request context information
     * 
     * @param string $url The request URL
     * @param mixed $parameter The request parameter
     * @return self
     */
    public function setRequestContext(string $url, $parameter = null): self
    {
        $this->requestUrl = $url;
        $this->requestParameter = $parameter;
        return $this;
    }

    /**
     * Get the request URL
     * 
     * @return string|null
     */
    public function getRequestUrl(): ?string
    {
        return $this->requestUrl;
    }

    /**
     * Get the request parameter
     * 
     * @return mixed
     */
    public function getRequestParameter()
    {
        return $this->requestParameter;
    }
}
