<?php
/**
 * Copyright (c) 2019.
 */

namespace App\Entity\Module;


class AverageModem extends Modem
{
    /**
     * @var Modem []
     */
    protected $modems = [];

    public function add(Modem $modem)
    {
        $this->modems[] = $modem;
        $cnt = 0;
        foreach ($this->modems as $modem) {
            $this->rssi += $modem->getRssi();
            $this->ber += $modem->getBer();
            $this->millis += $modem->getMillis();
            $this->MemoryFree += $modem->getMemoryFree();
            $cnt++;
        }
        $this->rssi = (int)($this->rssi / $cnt);
        $this->ber = (int)($this->ber / $cnt);
        $this->millis = (int)($this->millis/$cnt);
        $this->MemoryFree = (int) ($this->MemoryFree/$cnt);
    }

    /**
     * @return mixed
     */
    public function getRssi()
    {
        return $this->rssi;
    }

    /**
     * @return mixed
     */
    public function getBer()
    {
        return $this->ber;
    }

    /**
     * @return mixed
     */
    public function getMillis()
    {
        return $this->millis;
    }

    /**
     * @return mixed
     */
    public function getMemoryFree()
    {
        return $this->MemoryFree;
    }


}