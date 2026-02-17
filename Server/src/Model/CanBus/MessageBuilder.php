<?php
/*
 * Copyright (c) 2024.
 */

namespace App\Model\CanBus;

use App\Entity\CanBus\InvalidValueException;
use App\Entity\CanBus\Log\Line;
use App\Entity\CanBus\Message;
use App\Model\Converter;

/**
 * @class MessageBuilder
 */
class MessageBuilder
{
    protected $bytes;

    const OFFSET_FUEL = 0;

    const OFFSET_FUEL_ECONOMY = 1;

    const OFFSET_RPM = 3;
    const OFFSET_TEMP = 5;

    const OFFSET_DISTANCE = 6;

    const OFFSET_SPEED = 10;

    const OFFSET_BREAK = 11;

    const OFFSET_GAS = 12;

    const OFFSET_WEIGHT = 13;

    /**
     * @param string $messageBytes
     */
    public function __construct(string $messageBytes)
    {
        $this->bytes = $messageBytes;
    }

    /**
     * @param Line $logLine
     *
     * @return Message
     *
     */
    public function getMessage(Line $logLine): ?Message
    {
        try {
            $message = new Message();
            $message
                ->setFuel($this->getFuel())
                ->setFuelEconomy($this->getFuelEconomy())
                ->setRpm($this->getRPM())
                ->setTemp($this->getTemp())
                ->setDistance($this->getDistance())
                ->setSpeed($this->getSpeed())
                ->setBrakePedal($this->getBreakPedal())
                ->setGasPedal($this->getGasPedal())
                ->setWeight($this->getWeight());
            $message->setLog($logLine);

            return $message;
        } catch (InvalidValueException $exception) {
            return null;
        }
    }

    /**
     * @return float
     */
    protected function getFuel(): float
    {
        $i = self::OFFSET_FUEL;
        $fuel = ord($this->bytes[$i]) * 0.4;

        return $fuel;
    }

    /**
     * см. getFuelEconomy0();
     * @return float
     */
    protected function getFuelEconomy(): float
    {
        $i = self::OFFSET_FUEL_ECONOMY;
        $fuelEconomy = floor(Converter::convertCharsToLong2($this->bytes[$i].$this->bytes[$i + 1]) / 512);

        return $fuelEconomy;
    }

    /**
     * @return float
     *
     */
    protected function getRPM(): float
    {
        $i = static::OFFSET_RPM;
        $rpm = Converter::convertCharsToLong2($this->bytes[$i].$this->bytes[$i + 1]) * 0.125;

        return $rpm;
    }

    /**
     * @return int
     */
    protected function getTemp(): int
    {
        $i = static::OFFSET_TEMP;

        return ord($this->bytes[$i]) - 40;
    }

    /**
     * @return float
     */
    protected function getDistance(): float
    {
        $i = static::OFFSET_DISTANCE;
        $distance = Converter::convertCharsToLong2($this->bytes[$i].$this->bytes[$i + 1].$this->bytes[$i + 2].$this->bytes[$i + 3]) * 5 / 1000;

        return $distance;
    }

    /**
     * @return float
     */
    protected function getSpeed(): float
    {
        $i = static::OFFSET_SPEED;
        $speed = (ord($this->bytes[$i]) << 8) / 256;

        return $speed;
    }

    /**
     * @return int
     */
    protected function getBreakPedal(): float
    {
        $i = static::OFFSET_BREAK;
        $breakPedal = ord($this->bytes[$i]) * 0.4;

        return $breakPedal;
    }

    /**
     * @return float
     */
    protected function getGasPedal(): float
    {
        $i = static::OFFSET_GAS;
        $gasPedal = ord($this->bytes[$i]) * 0.4;

        return $gasPedal;
    }

    /**
     * @return int
     */
    protected function getWeight(): int
    {
        $i = static::OFFSET_WEIGHT;
        $weight = Converter::convertCharsToLong2($this->bytes[$i].$this->bytes[$i + 1]) * 10;

        return $weight;
    }
}
