<?php
/**
 * Copyright (c) 2019.
 */

namespace App\Entity\CanBus\Log;


use App\Entity\Module\AverageGPS;
use App\Entity\Module\AverageModem;
use App\Entity\Module\GPS;
use App\Entity\Module\Modem;

class AverageLine extends Line
{
    protected $lines = [];

    /**
     * @var GPS
     */
    protected $gps;

    /**
     * @var Modem
     */
    protected $modem;


    protected $averageMessageValues;


    public function add(Line $line)
    {
        $this->lines[] = $line;
        $sum = [];
        $cnt = 0;
        if (!$this->gps) {
            $this->gps = new AverageGPS($this, $line->getGps()->getMillis());
        }
        if (!$this->modem) {
            $this->modem = new AverageModem($this, $line->getModem()->getMillis());
        }
        $this->modem->add($line->getModem());
        $this->gps->add($line->getGps());


        foreach (self::PARAMS as $addr)
            $sum[$addr] = 0;
        foreach ($this->lines as $line) {
            foreach (self::PARAMS as $param => $addr) {
                $sum[$addr] += $line->getValue($addr);
            }
            $cnt++;
            $this->millis += $line->getMillis();
        }
        $this->millis /= $cnt;
        foreach ($sum as $addr => $value) {
            $this->averageMessageValues[$addr] = (int) ($value / $cnt);
        }
    }

    public function getValue($address)
    {
        return $this->averageMessageValues[$address];
    }

    /**
     * @return GPS
     */
    public function getGps(): GPS
    {
        return $this->gps;
    }

    /**
     * @return Modem
     */
    public function getModem(): Modem
    {
        return $this->modem;
    }

    /**
     * @return mixed
     */
    public function getMillis()
    {
        return (int)$this->millis;
    }


}