<?php

namespace App\Exceptions;

use Exception;

class InvalidQRCodeException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message = 'QR code tidak valid', int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
