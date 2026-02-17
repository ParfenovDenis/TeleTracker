<?php
/*
 * Copyright (c) 2024.
 */

namespace App\Doctrine\Persister;

use App\Entity\CanBus\Log\Line;
use App\Entity\CanBus\Message;
use App\Entity\HTTP\Request;
use App\Entity\Module\GPS;
use App\Entity\Module\Modem;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class SqlPersister extends AbstractPersister
{

    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var Line
     */
    private $line;

    /**
     * @var int
     */
    private $logId;

    const SQL_INSERT_LOG = "INSERT INTO log ( millis, requestId_id) VALUES (?,?)";
    const SQL_INSERT_MESSAGE = "INSERT INTO message (log_id, fuel, fuelEconomy, rpm, temp, distance, speed, brakePedal, gasPedal, weight) VALUES (?,?,?,?,?,?,?,?,?,?)";
    const SQL_INSERT_GPS = "INSERT INTO gps (log_id, datetime, latitude, longitude, altitude, speed, course, gpsSatellites, gnssSatellitesUsed, glonass, CN, millis) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    const SQL_INSERT_MODEM = "INSERT INTO modem (log_id, rssi, ber, millis, MemoryFree) VALUES (?,?,?,?,?)";

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
        $this->conn = $entityManager->getConnection();
    }


    public function persistLog(Line $line): PersisterInterface
    {
        $this->line = $line;

        return $this;
    }

    public function flush(): bool
    {

        $conn = $this->conn;
        try {
            $conn->beginTransaction();
            $this->logId = $this->insertLog();
            $this->insertMessage();
            $this->insertGps();
            $this->insertModem();
            $conn->commit();

            return true;
        } catch (ConnectionException $exception) {
            return false;
        }
    }


    protected function getParamsLog(): array
    {
        $line = $this->line;

        return [
            $line->getMillis(),
            $line->getRequestId()->getId(),
        ];
    }

    protected function insertLog(): int
    {
        $this->conn->executeQuery(self::SQL_INSERT_LOG, $this->getParamsLog());

        return $this->conn->lastInsertId();
    }

    protected function insertMessage(): void
    {
        $message = $this->line->getMessage();
        if ($message) {
            $this->conn->executeQuery(self::SQL_INSERT_MESSAGE, $this->getParamsMessage());
        }
    }

    protected function getParamsMessage(): array
    {
        $line = $this->line;
        $message = $line->getMessage();

        return [
            $this->logId,
            $message->getFuel(),
            $message->getFuelEconomy(),
            $message->getRpm(),
            $message->getTemp(),
            $message->getDistance(),
            $message->getSpeed(),
            $message->getBrakePedal(),
            $message->getGasPedal(),
            $message->getWeight(),
        ];
    }


    protected function getParamsGps(): array
    {
        $gps = $this->line->getGps();

        return [
            $this->logId,
            $gps->getDatetime() ? $gps->getDatetime()->format("Y-m-d H:i:s") : null,
            $gps->getLatitude(),
            $gps->getLongitude(),
            $gps->getAltitude(),
            $gps->getSpeed(),
            $gps->getCourse(),
            $gps->getGpsSatellites(),
            $gps->getGnssSatellitesUsed(),
            $gps->getGlonass(),
            $gps->getCN(),
            $gps->getMillis(),
        ];
    }

    protected function insertGps(): void
    {
        if ($this->line->getGps()) {
            $this->conn->executeQuery(self::SQL_INSERT_GPS, $this->getParamsGps());
        }
    }

    protected function getParamsModem(): array
    {
        $modem = $this->line->getModem();

        return [
            $this->logId,
            $modem->getRssi(),
            $modem->getBer(),
            $modem->getMillis(),
            $modem->getMemoryFree(),
        ];
    }

    protected function insertModem(): void
    {
        if ($this->line->getModem()) {
            $this->conn->executeQuery(self::SQL_INSERT_MODEM, $this->getParamsModem());
        }
    }
}
