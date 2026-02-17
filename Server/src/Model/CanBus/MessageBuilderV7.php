<?php
/*
 * Copyright (c) 2024.
 */

namespace App\Model\CanBus;

use App\Model\Converter;

class MessageBuilderV7 extends MessageBuilder
{


    /**
     * @return float
     */
    protected function getFuel(): float
    {
        $i = self::OFFSET_FUEL;

        $num = ord($this->bytes[$i]);
        $offset = 0x40;
        if ($num > 190)
            $offset = 0xC0;
        return (ord($this->bytes[$i]) - $offset) * 2.2;
    }

    /**
     * см. getFuelEconomy0();
     * @return float
     */
    protected function getFuelEconomy(): float
    {
        $i = self::OFFSET_FUEL_ECONOMY;
        $fuelEconomy = round(Converter::convertCharsToLong2($this->bytes[$i].$this->bytes[$i + 1])*0.05,2);

        return $fuelEconomy;
    }

    /**
     * @return int
     */
    protected function getBreakPedal(): float
    {
        $i = static::OFFSET_BREAK;
        $breakPedal = ord($this->bytes[$i]);

        return $breakPedal;
    }

    /**
     * @return int
     */
    protected function getWeight(): int
    {
        return 0;
    }

}
