<?php
/**
 * Copyright (c) 2019.
 */

namespace App\Entity\Module;


use App\Entity\CanBus\Log\Line;

class AverageGPS extends GPS
{
    /**
     * @var GPS []
     */
    protected $gps = [];

    public function add(GPS $gps)
    {
        $this->gps[] = $gps;
        $cnt = 0;
        foreach ($this->gps as $gps) {
            $this->datetime = $gps->getDatetime();
            $this->latitude += $gps->getLatitude();
            $this->longitude += $gps->getLongitude();
            $this->altitude += $gps->getAltitude();
            $this->speed += $gps->getSpeed();
            $this->course += $gps->getCourse();
            $this->gpsSatellites += $gps->getGpsSatellites();
            $this->gnssSatellitesUsed += $gps->getGnssSatellitesUsed();
            $this->glonass += $gps->getGlonass();
            $this->CN += $gps->getCN();
            $this->millis += $gps->getMillis();
            $cnt++;
        }
        $this->latitude = round($this->latitude / $cnt, 6);
        $this->longitude = round($this->longitude / $cnt,6);
        $this->altitude = (int)($this->altitude / $cnt);
        $this->speed = (int)($this->speed / $cnt);
        $this->course = round($this->course / $cnt, 2);
        $this->gpsSatellites = (int)($this->gpsSatellites / $cnt);
        $this->gnssSatellitesUsed = (int)($this->gnssSatellitesUsed / $cnt);
        $this->glonass = (int)($this->glonass / $cnt);
        $this->CN = (int)($this->CN / $cnt);
        $this->millis = (int)($this->millis / $cnt);
    }

    /**
     * @return mixed
     */
    public function getDatetime(): ?\DateTimeInterface
    {
        return $this->datetime;
    }

    /**
     * @return mixed
     */
    public function getLatitude($bytes = false)
    {
        return $this->latitude;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @return mixed
     */
    public function getAltitude()
    {
        return $this->altitude;
    }

    /**
     * @return mixed
     */
    public function getSpeed()
    {
        return $this->speed;
    }

    /**
     * @return mixed
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * @return mixed
     */
    public function getGpsSatellites()
    {
        return $this->gpsSatellites;
    }

    /**
     * @return mixed
     */
    public function getGnssSatellitesUsed()
    {
        return $this->gnssSatellitesUsed;
    }

    /**
     * @return mixed
     */
    public function getGlonass()
    {
        return $this->glonass;
    }

    /**
     * @return mixed
     */
    public function getCN()
    {
        return $this->CN;
    }

    /**
     * @return mixed
     */
    public function getMillis()
    {
        return $this->millis;
    }

}