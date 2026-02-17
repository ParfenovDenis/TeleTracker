<?php
/**
 * Copyright (c) 2020.
 */

namespace App\Model;


use App\Entity\CanBus\Log\Line;
use App\Entity\CanBus\Message;
use App\Entity\HTTP\Request;
use App\Entity\Module\GPS;
use App\Entity\Module\Modem;

/**
 * Class ObjectBuilder
 * @package App\Model
 */
class ObjectBuilderV2 extends ObjectBuilder
{
    const MIN_BYTES_LENGTH = 72;
    const IMEI_LENGTH = 15;

    const VERSION = '2';

    public function getImei(): string
    {
        return substr($this->bytes,0,self::IMEI_LENGTH);
    }
}