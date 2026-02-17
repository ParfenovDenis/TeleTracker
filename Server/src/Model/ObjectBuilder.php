<?php
/**
 * Copyright (c) 2019.
 */

namespace App\Model;

use App\Entity\CanBus\Log\Line;
use App\Entity\CanBus\Message;
use App\Entity\HTTP\Request;
use App\Entity\Module\GPS;
use App\Entity\Module\Modem;

/**
 * Class ObjectBuilder
 *
 */
class ObjectBuilder extends AbstractObjectBuilder
{

    public function getImei(): string
    {
        $imei1 = (ord($this->bytes[0]) | (ord($this->bytes[1]) >> 6 << 8));
        $byte1 = ord($this->bytes[1]);
        if ($byte1 >= 128)
            $byte1 -= 128;
        if ($byte1 >= 64)
            $byte1 -= 64;
        $imei2 = ord($this->bytes[2]) << 16 | ord($this->bytes[3]) << 8 | ord($this->bytes[4]) | $byte1 << 24;
        $imei = self::IMEI_PREFIX . (string)$imei1 . (string)$imei2;

        return $imei;
    }

    /**
     * @return AbstractObjectBuilder
     */
    public function build0(): AbstractObjectBuilder
    {
        $request = $this->getRequest();
        if (!count($request->getLogs())) {

            $length = \strlen($this->bytes);
            $i = static::IMEI_LENGTH + static::FP_LENGTH - 1;

            while (($i + static::LOG_LINE_LENGTH) < $length) {
                $logLine = new Line();
                $logLine->setMillis(Converter::convertCharsToLong($this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i]));
                $logLine->setRequestId($request);
                $message = new Message();

                $message
                    ->setFuel(ord($this->bytes[++$i]) * 0.4)
                    ->setFuelEconomy($this->getFuelEconomy0($this->bytes[++$i] . $this->bytes[++$i]))
                    ->setRpm(Converter::convertCharsToLong2($this->bytes[++$i] . $this->bytes[++$i]) * 0.125)
                    ->setTemp(ord($this->bytes[++$i]) - 40)
                    ->setDistance(Converter::convertCharsToLong2($this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i]) * 5 / 1000)
                    ->setSpeed((ord($this->bytes[++$i]) << 8) / 256)
                    ->setBrakePedal(ord($this->bytes[++$i]) * 0.4)
                    ->setGasPedal(ord($this->bytes[++$i]) * 0.4)
                    ->setWeight(Converter::convertCharsToLong2($this->bytes[++$i] . $this->bytes[++$i]) * 10)
                    ->setLog($logLine);
                $gps = new GPS($logLine);
                $gps->setDatetime(Converter::getDateTime($this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i]));
                $latitudeLong = Converter::convertCharsToLong($this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i]);
                $del = pow(10, (strlen((string)$latitudeLong) - 2));
                $gps->setLatitude($latitudeLong / $del);
                $longitudeLong = Converter::convertCharsToLong($this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i]);
                $del = pow(10, (strlen((string)$longitudeLong) - 2));
                $gps->setLongitude($longitudeLong / $del)
                    ->setAltitude(Converter::convertCharsToLong($this->bytes[++$i] . $this->bytes[++$i]))
                    ->setSpeed(ord($this->bytes[++$i]))
                    ->setCourse(Converter::convertCharsToLong($this->bytes[++$i] . $this->bytes[++$i]))
                    ->setGpsSatellites(ord($this->bytes[++$i]))
                    ->setGnssSatellitesUsed(ord($this->bytes[++$i]))
                    ->setGlonass(ord($this->bytes[++$i]))
                    ->setCN(ord($this->bytes[++$i]))
                    ->setMillis(Converter::convertCharsToLong($this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i]))
                    ->setLog($logLine);
                $modem = new Modem($logLine);
                $modem
                    ->setRssi(ord($this->bytes[++$i]))
                    ->setBer(ord($this->bytes[++$i]))
                    ->setMillis(Converter::convertCharsToLong($this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i]))
                    ->setMemoryFree(Converter::convertCharsToLong($this->bytes[++$i] . $this->bytes[++$i]))
                    ->setLog($logLine);

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
            }
        }
        return $this;
    }

    protected function initOffset(): void
    {
        $this->offset = static::IMEI_LENGTH + static::FP_LENGTH;
    }



    protected function getFuelEconomy0($bytes): float
    {
        return floor(Converter::convertCharsToLong2($bytes[0] . $bytes[1]) / 512);
    }

    protected function getFuelEconomy(): float
    {

        $i = $this->offset + static::OFFSET_FUEL_ECONOMY;
        $value = floor(Converter::convertCharsToLong2($this->bytes[$i] . $this->bytes[$i + 1]) / 512);

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

        return ord($this->bytes[$i]);
    }

    protected function getGasPedal(): float
    {
        $i = $this->offset + static::OFFSET_GAS;
        return ord($this->bytes[$i]) * 0.4;
    }

    protected function getWeight(): int
    {
        $i = $this->offset + static::OFFSET_WEIGHT;
        return Converter::convertCharsToLong2($this->bytes[$i] . $this->bytes[$i + 1]) * 10;
    }




    public function getContentBytes(): string
    {
        $pos = static::IMEI_LENGTH + static::FP_LENGTH;
        foreach ($this->gps as $gps) {
            if ($pos + static::LOG_LINE_LENGTH >= strlen($this->bytes))
                $this->bytes .= str_repeat(chr(null), $pos - strlen($this->bytes));
            //$gpsBytes = $gps->get
            //    $this->bytes = substr_replace($this->bytes, $pos,);
        }
        return $this->bytes;

    }


}