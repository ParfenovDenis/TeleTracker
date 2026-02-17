<?php
/*
 * Copyright (c) 2024.
 */

namespace App\Model\Exception;

/**
 * Недостаточно байтов
 */
class NotEnoughBytesException extends \RuntimeException
{
    protected $offset;
    protected $needBytes;
    protected $cntBytes;

    public function __construct($offset, $needBytes, $cntBytes, string $code)
    {
        $this->offset = $offset;
        $this->needBytes = $needBytes;
        $this->cntBytes = $cntBytes;
        $message = '$offset  = ' . $this->offset . "\r\n";
        $message .= '$needBytes = ' . $this->needBytes . "\r\n";
        $message .= '$cntBytes = ' . $this->cntBytes . "\r\n";
        $message .= '$code = ' . $code . "\r\n";
        parent::__construct($message);
    }
}
