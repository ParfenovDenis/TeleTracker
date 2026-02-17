<?php
/*
 * Copyright (c) 2024.
 */

namespace App\Model\GPS;

use App\Entity\CanBus\Log\Line;
use App\Entity\Module\GPS;
use App\Model\Converter;
use App\Model\Validator\CoordinateValidator;
use App\Model\Validator\CourseValidator;
use App\Model\Validator\Exception\InvalidCoordinateException;
use App\Model\Validator\Exception\InvalidCourseException;
use App\Model\Validator\Exception\InvalidDateTimeException;

/**
 * @class GpsBuilder
 */
class GpsBuilder
{
    private $bytes;

    const OFFSET_GPS_DATETIME = 0;

    const OFFSET_LATITUDE = 5;

    const OFFSET_LONGTUDE = 9;

    const OFFSET_ALTITUDE = 13;

    const OFFSET_GPS_SPEED = 15;

    const OFFSET_COURSE = 16;

    const OFFSET_SATELLITES = 18;

    const OFFSET_SATELLITES_USED = 19;

    const OFFSET_GLONASS = 20;

    const OFFSET_CN = 21;

    const OFFSET_GPS_MILLIS = 22;


    /**
     * @param string $gpsBytes
     */
    public function __construct(string $gpsBytes)
    {
        $this->bytes = $gpsBytes;
    }

    /**
     * @param Line $logLine
     *
     * @return GPS
     */
    public function getGps(Line $logLine): GPS
    {
        $gps = new GPS($logLine);
        $gps
            ->setDatetime($this->getGpsDateTime())
            ->setLatitude($this->getLatitude())
            ->setLongitude($this->getLongitude())
            ->setAltitude($this->getAltitude())
            ->setSpeed($this->getGpsSpeed())
            ->setCourse($this->getCourse())
            ->setGpsSatellites($this->getGpsSatellites())
            ->setGnssSatellitesUsed($this->getGnssSatellitesUsed())
            ->setGlonass($this->getGlonass())
            ->setCN($this->getCN())
            ->setMillis($this->getGpsMillis());

        return $gps;
    }

    /**
     * @return \DateTimeInterface|null
     */
    protected function getGpsDateTime(): ?\DateTimeInterface
    {
        $i = static::OFFSET_GPS_DATETIME;
        try {
            $dateTime = Converter::getDateTime($this->bytes[$i].$this->bytes[$i + 1].$this->bytes[$i + 2].$this->bytes[$i + 3].$this->bytes[$i + 4]);
        } catch (InvalidDateTimeException $exception) {
            $dateTime = null;
        }

        return $dateTime;
    }

    /**
     * @return float|null
     */
    protected function getLatitude(): ?float
    {
        $latitude = $this->getCoordinate(static::OFFSET_LATITUDE);
        try {
            CoordinateValidator::validateLatitude($latitude);
        } catch (InvalidCoordinateException $exception) {
            $latitude = null;
        }

        return $latitude;
    }

    /**
     * @return float|null
     */
    protected function getLongitude(): ?float
    {
        $longitude = $this->getCoordinate(static::OFFSET_LONGTUDE);
        try {
            CoordinateValidator::validateLongitude($longitude);
        } catch (InvalidCoordinateException $exception) {
            $longitude = null;
        }

        return $longitude;
    }

    /**
     * @param $i
     *
     * @return float
     */
    protected function getCoordinate($i): float
    {
        $coordinateLong = Converter::convertCharsToLong($this->bytes[$i].$this->bytes[$i + 1].$this->bytes[$i + 2].$this->bytes[$i + 3]);
        $del = pow(10, (strlen((string) $coordinateLong) - 2));

        return $coordinateLong / $del;
    }

    /**
     * @return int
     */
    protected function getAltitude(): int
    {
        $i = static::OFFSET_ALTITUDE;

        return Converter::convertCharsToLong($this->bytes[$i].$this->bytes[$i + 1]);
    }

    /**
     * @return int
     */
    protected function getGpsSpeed(): int
    {
        $i = static::OFFSET_GPS_SPEED;

        return ord($this->bytes[$i]);
    }

    /**
     * @return int
     */
    protected function getCourse(): ?int
    {
        $i = static::OFFSET_COURSE;
        $course = Converter::convertCharsToLong($this->bytes[$i].$this->bytes[$i + 1]);
        try {
            CourseValidator::validate($course);
        } catch (InvalidCourseException $exception) {
            $course = null;
        }

        return $course;
    }

    /**
     * @return int
     */
    protected function getGpsSatellites(): int
    {
        $i = static::OFFSET_SATELLITES;

        return ord($this->bytes[$i]);
    }

    /**
     * @return int
     */
    protected function getGnssSatellitesUsed(): int
    {
        $i = static::OFFSET_SATELLITES_USED;

        return ord($this->bytes[$i]);
    }

    /**
     * @return int
     */
    protected function getGlonass(): int
    {
        $i = static::OFFSET_GLONASS;

        return ord($this->bytes[$i]);
    }

    /**
     * @return int
     */
    protected function getCN(): int
    {
        $i = static::OFFSET_CN;

        return ord($this->bytes[$i]);
    }

    /**
     * @return int
     */
    protected function getGpsMillis(): int
    {
        $i = static::OFFSET_GPS_MILLIS;

        return Converter::convertCharsToLong($this->bytes[$i].$this->bytes[$i + 1].$this->bytes[$i + 2].$this->bytes[$i + 3]);
    }

}