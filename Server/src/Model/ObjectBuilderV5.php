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
use App\Model\CanBus\MessageBuilderV5;

class ObjectBuilderV5 extends ObjectBuilderV4
{
    const VERSION = '5';

    const MIN_BYTES_LENGTH = 68;

    const LOG_LINE_LENGTH = 49;

    const POST_DATA_SIZE = 9966;

    const FINISH_MESSAGE = 14;

    const START_GPS = 15;

    const FINISH_GPS = 40;

    const START_MODEM = 41;

    const FINISH_MODEM = 48;

    const THEAD = [
        'tank' => 'tank',
        'distance' => 'distance',
        'fuel_economy' => 'fuel_economy2',
        'rpm' => 'rpm',
        'temperature' => 'temperature',
        'speedometer' => 'speedometer',
    ];

    /**
     * @return MessageBuilder
     */
    protected function getMessageBuilder(): MessageBuilder
    {
        return new MessageBuilderV5($this->getMessageBytes());
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
                    ->setFuelEconomy(Converter::convertCharsToLong2($this->bytes[++$i] . $this->bytes[++$i]) / 100) //  л/ч
                    ->setRpm(ord($this->bytes[++$i]) * 256 / 4)
                    ->setTemp(ord($this->bytes[++$i]) - 40)
                    ->setDistance(Converter::convertCharsToLong($this->bytes[++$i] . $this->bytes[++$i])) // км
                    ->setSpeed(ord($this->bytes[++$i]))
                    ->setBrakePedal(Converter::convertCharsToLong2($this->bytes[++$i] . $this->bytes[++$i]) / 100) //МПа
                    ->setGasPedal(ord($this->bytes[++$i]) * 100 / 255)
                    ->setWeight(0)
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
}