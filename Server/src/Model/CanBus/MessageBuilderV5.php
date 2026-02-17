<?php
/*
 * Copyright (c) 2024.
 */

namespace App\Model\CanBus;

use App\Model\Converter;

/**
 * @class MessageBuilderV5
 */
class MessageBuilderV5 extends MessageBuilder
{

    const OFFSET_TEMP = 4;

    const OFFSET_DISTANCE = 5;

    const OFFSET_SPEED = 7;

    const OFFSET_BREAK = 8;

    const OFFSET_GAS = 10;

    /**
     * @return float
     */
    protected function getFuelEconomy(): float
    {
        $i = self::OFFSET_FUEL_ECONOMY;
        $fuelEconomy = Converter::convertCharsToLong2($this->bytes[$i].$this->bytes[$i + 1]) / 100;

        return $fuelEconomy;
    }

    /**
     * @return float
     */
    protected function getRPM(): float
    {
        $i = static::OFFSET_RPM;
        $rpm = Converter::convertCharsToLong2($this->bytes[$i]) * 256 / 4;

        return $rpm;
    }

    /**
     * @return float
     */
    protected function getDistance(): float
    {
        $i = static::OFFSET_DISTANCE;


        $distance = Converter::convertCharsToLong($this->bytes[$i].$this->bytes[$i + 1]) ;

        return $distance;
    }

    /**
     * @return float
     */
  protected function getBreakPedal(): float
    {
        $i = static::OFFSET_BREAK;
        $breakPedal = Converter::convertCharsToLong2($this->bytes[$i].$this->bytes[$i+1]) / 100;

        return $breakPedal;
    }

    /**
     * @return float
     */
    protected function getGasPedal(): float
    {
        $i = static::OFFSET_GAS;
        $gasPedal = ord($this->bytes[$i])  * 100 / 255;

        return $gasPedal;
    }


    protected function getWeight(): int
    {
        return 0;
    }



}