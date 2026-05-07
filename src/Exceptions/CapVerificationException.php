<?php

namespace LaravelCap\Exceptions;

use Exception;

class CapVerificationException extends Exception
{
    public function __construct(string $message = 'Cap verification failed.')
    {
        parent::__construct($message);
    }
}
