<?php
/*
 * Copyright (c) 2026.
 */

namespace App\Repository\Customers\Validator\Company\Exception;

class ValidationException extends \Exception
{

    public function __construct(array $messages)
    {
        $message = implode(', ', $messages);
        parent::__construct($message);
    }

    /**
     * @return mixed
     */
    public function getMessages(): array
    {
        return $this->message;
    }
}