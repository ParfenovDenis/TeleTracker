<?php
/**
 * Copyright (c) 2019.
 */

namespace App\Model;


use App\Entity\CanBus\Log\Line;
use App\Entity\Module\GPS;
use App\Entity\Module\Modem;

class LogFile
{
    /**
     * @var \App\Entity\CanBus\Log\Line []
     */
    protected $lines = [];

    /**
     * LogFile constructor.
     * @throws \Exception
     * @param resource $h             Handler of file
     * @param boolean  $isRequestFile is request file?
     */
    public function __construct($h, $isRequestFile = true)
    {
        if (!is_resource($h)) {
            throw new \Exception('is not resource');
        }
        if ($isRequestFile === true) {
            fseek($h, 9);
        }

        while ($line = fread($h, 53)) {
            if ($line) {


                $log = new Line();
                $message = '';
                for ($i = 4; $i < 19; $i++)
                    $message .= $line[$i];
                $log->setMessage($message);
                $log->setMillis($line[0] . $line[1] . $line[2] . $line[3]);
                $gps = new GPS($log, $line[41] . $line[42] . $line[43] . $line[44]);

                $gps->setDateTimeBytes($line[19] . $line[20]. $line[21]. $line[22]. $line[23] )
                    ->setLatitude($line[24] . $line[25] . $line[26] . $line[27])
                    ->setLongitude($line[28] . $line[29] . $line[30] . $line[31])
                    ->setAltitude($line[32] . $line[33])
                    ->setSpeed($line[34])
                    ->setCourse($line[35] . $line[36])
                    ->setGpsSatellites($line[37])
                    ->setGnssSatellitesUsed($line[38])
                    ->setGlonass($line[39])
                    ->setCN($line[40]);
                $millisModem = $line[47] . $line[48] . $line[49] . $line[50];
                $modem = new Modem($log, $millisModem);
                $modem->setRssi($line[45])
                    ->setBer($line[46])
                    ->setMemoryFree($line[51] . $line[52]);

                $log->setGps($gps)->setModem($modem);
                $this->lines[] = $log;
            }
        }
    }

    /**
     * @return Line []
     */
    public function getLines()
    {
        return $this->lines;
    }


}