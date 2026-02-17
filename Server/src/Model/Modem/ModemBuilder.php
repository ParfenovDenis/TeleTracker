<?php
/*
 * Copyright (c) 2024.
 */

namespace App\Model\Modem;

use App\Entity\CanBus\Log\Line;
use App\Entity\Module\GPS;
use App\Entity\Module\Modem;
use App\Model\Converter;

class ModemBuilder
{
    private $bytes;

    const OFFSET_RSSI = 0;

    const OFFSET_BER = 1;

    const OFFSET_MILLIS = 2;

    const OFFSET_MEMORY_FREE = 6;


    public function __construct($modemBytes)
    {
        $this->bytes = $modemBytes;
    }

    public function getModem(Line $logLine): Modem
    {
        $modem = new Modem($logLine);
        $modem
            ->setRssi($this->getRssi())
            ->setBer($this->getBer())
            ->setMillis($this->getMillis())
            ->setMemoryFree($this->getMemoryFree());

        return $modem;
    }

    protected function getRssi()
    {
        return ord($this->bytes[self::OFFSET_RSSI]);
    }

    protected function getBer()
    {
        return ord($this->bytes[self::OFFSET_BER]);
    }

    protected function getMillis(): int
    {
        $i = static::OFFSET_MILLIS;

        return Converter::convertCharsToLong($this->bytes[$i].$this->bytes[$i + 1].$this->bytes[$i + 2].$this->bytes[$i + 3]);
    }

    protected function getMemoryFree(): int
    {
        $i = static::OFFSET_MEMORY_FREE;
        return Converter::convertCharsToLong($this->bytes[$i] . $this->bytes[$i + 1]);
    }
}