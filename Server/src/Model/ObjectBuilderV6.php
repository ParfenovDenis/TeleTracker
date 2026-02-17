<?php
/**
 * Copyright (c) 2021.
 */

namespace App\Model;


class ObjectBuilderV6 extends ObjectBuilderV4
{
    const VERSION = '6';

    const THEAD = [
        'tank' => 'tank',
        'distance' => 'distance',
        'fuel_economy' => 'fuel_economy2',
        'rpm' => 'rpm',
        'temperature' => 'temperature',
        'speedometer' => 'speedometer',
    ];

    protected function getFuelEconomy(): float
    {

        $i = $this->offset + static::OFFSET_FUEL_ECONOMY;
        $value = round(Converter::convertCharsToLong2($this->bytes[$i] . $this->bytes[$i+1]) * 0.05,2);
        return $value;
    }
}