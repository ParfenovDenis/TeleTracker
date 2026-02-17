<?php
/**
 * Copyright (c) 2021.
 */

namespace App\Model;


use App\Entity\CanBus\Log\Line;
use App\Entity\CanBus\Message;
use App\Entity\Module\GPS;
use App\Entity\Module\Modem;
use App\Model\CanBus\MessageBuilder;
use App\Model\CanBus\MessageBuilderV7;

class ObjectBuilderV7 extends ObjectBuilderV6
{
    const VERSION = '7';

    const OFFSET_MILLIS = 0;

    const OFFSET_FUEL = 4;

    const OFFSET_FUEL_ECONOMY = 5;

    const OFFSET_RPM = 7;

    const OFFSET_TEMP = 9;

    const OFFSET_DISTANCE = 10;

    const OFFSET_SPEED = 14;

    const OFFSET_BREAK = 15;

    const OFFSET_GAS = 16;

    const OFFSET_GPS_DATETIME = 17;

    const OFFSET_LATITUDE = 22;

    const OFFSET_LONGTUDE = 26;

    const OFFSET_ALTITUDE = 30;

    const OFFSET_GPS_SPEED = 32;

    const OFFSET_COURSE = 33;

    const OFFSET_SATELLITES = 35;

    const OFFSET_SATELLITES_USED = 36;

    const OFFSET_GLONASS = 37;

    const OFFSET_CN = 38;

    const OFFSET_GPS_MILLIS = 39;

    const OFFSET_RSSI = 43;

    const OFFSET_BER = 44;

    const OFFSET_MODEM_MILLIS = 45;

    const OFFSET_MEMORY = 49;
    const FINISH_MESSAGE = 16;
    const START_GPS = 17;

    const FINISH_GPS = 42;

    const START_MODEM = 43;

    const FINISH_MODEM = 50;

    const THEAD = [
        'tank' => 'tank',
        'distance' => 'distance',
        'fuel_economy' => 'fuel_economy2',
        'rpm' => 'rpm',
        'temperature' => 'temperature',
        'speedometer' => 'speedometer',
    ];

    const LOG_LINE_LENGTH = 51;

    const MIN_BYTES_LENGTH = 70;

    const  POST_DATA_SIZE = 9607;


    public function __construct(string $bytes)
    {
        parent::__construct($bytes);
        $this->offset = static::IMEI_LENGTH + static::FP_LENGTH;
    }

    /**
     * @param string $bytes
     * @return ObjectBuilderV7
     */
    public function setBytes(string $bytes): ObjectBuilderV7
    {
        $this->bytes = $bytes;
        return $this;
    }


    /**
     * @return AbstractObjectBuilder
     */
    public function build0(): AbstractObjectBuilder
    {
        $request = $this->getRequest();
        if (!count($request->getLogs())) {
            $length = \strlen($this->bytes);
            while ($this->offset < $length) {
                $logLine = new Line();
                $logLine->setMillis($this->getMillis());
                $logLine->setRequestId($request);
                $message = $this->getMessage();
                $message->setLog($logLine);
                $gps = $this->getGPS($logLine);
                $modem = $this->getModem($logLine);
                $logLine
                    ->setMessage($message)
                    ->setGps($gps)
                    ->setModem($modem);

                if ($gps->getLatitude() > 99)
                    $gps->setLatitude(0);
                if ($gps->getLongitude() > 999)
                    $gps->setLongitude(0);
                if ($gps->getSpeed() > 999)
                    $gps->setSpeed(0);
                if ($gps->getCourse() > 999)
                    $gps->setCourse(0);

                $request->addLog($logLine);
                $this->offset += static::LOG_LINE_LENGTH;
            }
        }
        return $this;
    }

    /**
     * @return MessageBuilder
     */
    protected function getMessageBuilder(): MessageBuilder
    {
        return new MessageBuilderV7($this->getMessageBytes());
    }

    protected function getMillis(): int
    {
        $i = $this->offset + static::OFFSET_MILLIS;
        return Converter::convertCharsToLong($this->bytes[$i] . $this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i]);
    }

    protected function getFuel(): float
    {
        $i = $this->offset + static::OFFSET_FUEL;
        $num = ord($this->bytes[$i]);
        $offset = 0x40;
        if ($num > 190)
            $offset = 0xC0;
        return (ord($this->bytes[$i]) - $offset) * 2.2;
    }

    protected function getFuelEconomy2(): float
    {
        $i = $this->offset + static::OFFSET_FUEL_ECONOMY;
        $value = round(Converter::convertCharsToLong2($this->bytes[$i] . $this->bytes[$i + 1]) * 0.05, 2);
        return $value;
    }

    protected function getRPM(): float
    {
        $i = $this->offset + static::OFFSET_RPM;
        $rpm = Converter::convertCharsToLong2($this->bytes[$i] . $this->bytes[$i + 1]) * 0.125;

        return $rpm;
    }

    protected function getTemp(): int
    {
        $i = $this->offset + static::OFFSET_TEMP;
        return ord($this->bytes[$i]) - 40;
    }

    protected function getDistance(): float
    {
        $i = $this->offset + static::OFFSET_DISTANCE;
        return Converter::convertCharsToLong2($this->bytes[$i] . $this->bytes[$i + 1] . $this->bytes[$i + 2] . $this->bytes[$i + 3]) * 5 / 1000;
    }

    protected function getSpeed(): float
    {
        $i = $this->offset + static::OFFSET_SPEED;
        return (ord($this->bytes[$i]) << 8) / 256;
    }

    protected function getBreakPedal(): int
    {
        $i = $this->offset + static::OFFSET_BREAK;
        $num = $this->bytes[$i];
        $ord = ord($this->bytes[$i]);
        return ord($this->bytes[$i]);
    }

    protected function getGasPedal(): float
    {
        $i = $this->offset + static::OFFSET_GAS;
        return ord($this->bytes[$i]) * 0.4;
    }

    protected function getMessage(): Message
    {
        $message = new Message();

        $message
            ->setFuel($this->getFuel())
            ->setFuelEconomy($this->getFuelEconomy2())
            ->setRpm($this->getRPM())
            ->setTemp($this->getTemp())
            ->setDistance($this->getDistance())
            ->setSpeed($this->getSpeed())
            ->setBrakePedal($this->getBreakPedal())
            ->setGasPedal($this->getGasPedal())
            ->setWeight(0);

        return $message;
    }

    protected function getGpsDateTime(): \DateTimeInterface
    {
        $i = $this->offset + static::OFFSET_GPS_DATETIME;
        return Converter::getDateTime($this->bytes[$i] . $this->bytes[$i + 1] . $this->bytes[$i + 2] . $this->bytes[$i + 3] . $this->bytes[$i + 4]);
    }

    protected function getLatitude(): float
    {
        $i = $this->offset + static::OFFSET_LATITUDE;
        $latitudeLong = Converter::convertCharsToLong($this->bytes[$i] . $this->bytes[$i + 1] . $this->bytes[$i + 2] . $this->bytes[$i + 3]);
        $del = pow(10, (strlen((string)$latitudeLong) - 2));
        return $latitudeLong / $del;
    }

    protected function getLongitude(): float
    {
        $i = $this->offset + static::OFFSET_LONGTUDE;
        $longitudeLong = Converter::convertCharsToLong($this->bytes[$i] . $this->bytes[$i + 1] . $this->bytes[$i + 2] . $this->bytes[$i + 3]);
        $del = pow(10, (strlen((string)$longitudeLong) - 2));
        return $longitudeLong / $del;
    }

    protected function getAltitude(): int
    {
        $i = $this->offset + static::OFFSET_ALTITUDE;
        return Converter::convertCharsToLong($this->bytes[$i] . $this->bytes[$i + 1]);
    }

    protected function getGpsSpeed(): int
    {
        $i = $this->offset + static::OFFSET_GPS_SPEED;

        return ord($this->bytes[$i]);
    }

    protected function getCourse(): int
    {
        $i = $this->offset + static::OFFSET_COURSE;
        return Converter::convertCharsToLong($this->bytes[$i] . $this->bytes[$i + 1]);
    }

    protected function getGpsSatellites(): int
    {
        $i = $this->offset + static::OFFSET_SATELLITES;

        return ord($this->bytes[$i]);
    }

    protected function getGnssSatellitesUsed(): int
    {
        $i = $this->offset + static::OFFSET_SATELLITES_USED;

        return ord($this->bytes[$i]);
    }

    protected function getGlonass(): int
    {
        $i = $this->offset + static::OFFSET_GLONASS;

        return ord($this->bytes[$i]);
    }

    protected function getCN(): int
    {
        $i = $this->offset + static::OFFSET_CN;

        return ord($this->bytes[$i]);
    }

    protected function getGpsMillis(): int
    {
        $i = $this->offset + static::OFFSET_GPS_MILLIS;

        return Converter::convertCharsToLong($this->bytes[$i] . $this->bytes[$i + 1] . $this->bytes[$i + 2] . $this->bytes[$i + 3]);
    }

    protected function getGPS(Line $logLine): GPS
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


    protected function getRssi(): int
    {
        $i = $this->offset + static::OFFSET_RSSI;

        return ord($this->bytes[$i]);
    }

    protected function getBer(): int
    {
        $i = $this->offset + static::OFFSET_BER;

        return ord($this->bytes[$i]);
    }

    protected function getModemMillis(): int
    {
        $i = $this->offset + static::OFFSET_MODEM_MILLIS;

        return Converter::convertCharsToLong($this->bytes[$i] . $this->bytes[$i + 1] . $this->bytes[$i + 2] . $this->bytes[$i + 3]);
    }

    protected function getMemoryFree(): int
    {
        $i = $this->offset + static::OFFSET_MEMORY;
        return Converter::convertCharsToLong($this->bytes[$i] . $this->bytes[$i + 1]);
    }

    protected function getModem(Line $logLine): Modem
    {
        $modem = new Modem($logLine);
        $modem
            ->setRssi($this->getRssi())
            ->setBer($this->getBer())
            ->setMillis($this->getModemMillis())
            ->setMemoryFree($this->getMemoryFree());

        return $modem;
    }
}