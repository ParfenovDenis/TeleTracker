<?php
/**
 * Copyright (c) 2019.
 */

namespace App\Repository\CanBus;

use App\Entity\CanBus\Log\Line;

use App\Repository\RepositoryInterface;
use App\Repository\RepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


use Doctrine\Persistence\ManagerRegistry;



/**
 * @method Line|null find($id, $lockMode = null, $lockVersion = null)
 * @method Line|null findOneBy(array $criteria, array $orderBy = null)
 * @method Line[]    findAll()
 * @method Line[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository implements RepositoryInterface
{
    private static $id = [];

    use RepositoryTrait;

    private $selectAll = "  SELECT r.datetime AS requestDateTime, r.id as requestId, r.version,
    l.id as logId, l.millis AS logMillis, 
    DATE_ADD(g.datetime, INTERVAL 3 HOUR) AS GPSDateTime, g.latitude, g.longitude, g.altitude, g.speed AS GPSspeed, g.course, g.gpsSatellites, g.gnssSatellitesUsed, g.glonass, 
    g.CN, g.millis AS GPSmillis, m.fuel,  ROUND(m.fuelEconomy,3) AS  fuelEconomy, ROUND(m.rpm) AS rpm, m.temp, 
       IF(ISNULL(o.distance) > 0, m.distance, o.distance + m.distance) AS distance,  
        ROUND( m.speed, 2) AS CANspeed, m.brakePedal, m.gasPedal, 
    m.weight, m1.rssi, m1.ber, m1.millis AS modemMillis, m1.MemoryFree   
    FROM log l    
        LEFT JOIN request r ON l.requestId_id = r.id
        INNER JOIN gps g ON l.id = g.log_id
        INNER JOIN message m ON l.id = m.log_id
        INNER JOIN modem m1 ON l.id = m1.log_id
         INNER JOIN imeitruck i ON r.imei = i.imei 
        LEFT JOIN offset o    ON o.truck_id = i.truck_id AND o.dateTimeFrom <= :dateTimeTo
AND (
o.dateTimeTo >= :dateTimeFrom
OR o.dateTimeTo IS NULL
)
        WHERE  i.truck_id  = :truck_id
 AND (g.datetime BETWEEN :dateTimeFrom AND :dateTimeTo)

  
  AND i.DateTimeFrom <= :dateTimeTo AND
  (i.DateTimeTo >= :dateTimeFrom OR i.DateTimeTo IS NULL) 
    
    ";

    private $selectLast = " SELECT r.datetime AS requestDateTime, r.id as requestId, r.version,
    l.id as logId, l.millis AS logMillis, 
    DATE_ADD(g.datetime, INTERVAL 3 HOUR) AS GPSDateTime, g.latitude, g.longitude, g.altitude, g.speed AS GPSspeed, g.course, g.gpsSatellites, g.gnssSatellitesUsed, g.glonass, 
    g.CN, g.millis AS GPSmillis, m.fuel,  ROUND(m.fuelEconomy,3) AS  fuelEconomy, ROUND(m.rpm) AS rpm, m.temp, 
       IF(ISNULL(o.distance) > 0, m.distance, o.distance + m.distance) AS distance,  
        ROUND( m.speed, 2) AS CANspeed, m.brakePedal, m.gasPedal, 
    m.weight, m1.rssi, m1.ber, m1.millis AS modemMillis, m1.MemoryFree
    
    FROM log l 
    
    
        LEFT JOIN request r ON l.requestId_id = r.id
        INNER JOIN gps g ON l.id = g.log_id
        INNER JOIN message m ON l.id = m.log_id
        INNER JOIN modem m1 ON l.id = m1.log_id
         INNER JOIN imeitruck i ON r.imei = i.imei 
        LEFT JOIN offset o    ON o.truck_id = i.truck_id AND o.dateTimeFrom <= :dateTimeTo
AND (
o.dateTimeTo >= :dateTimeFrom
 OR o.dateTimeTo IS NULL
)
        WHERE  i.truck_id 

  IN (SELECT  i.truck_id
  FROM log l 
  INNER JOIN request r ON l.requestId_id = r.id
  INNER JOIN imeitruck i ON r.imei = i.imei
  WHERE l.id = :log_id) AND (g.datetime BETWEEN :dateTimeFrom AND :dateTimeTo)

  
  AND i.DateTimeFrom <= :dateTimeTo AND
  (i.DateTimeTo >= :dateTimeFrom OR i.DateTimeTo IS NULL) AND g.log_id > :log_id ";

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Line::class);

    }

    private function getSQL($last = false): string
    {
        return $last ? $this->selectLast : $this->selectAll;
    }

    public function getLogs(\DateTime $dateTime, $truckId): array
    {
        if ($dateTime === null)
            $dateTime = new \DateTime();
        $dateTime->setTime(0, 0, 0);
        $dateTo = clone $dateTime;
        $dateTo->setTime(23, 59, 59);
        $params = [
            'truck_id' => $truckId,
            'dateTimeFrom' => $dateTime->format('Y-m-d H:i:s'),
            'dateTimeTo' => $dateTo->format('Y-m-d H:i:s'),
        ];

        $sql = $this->getSQL()
        ;
        $sql = str_replace("AND (g.datetime BETWEEN :dateTimeFrom AND :dateTimeTo)", "AND (r.datetime BETWEEN :dateTimeFrom AND :dateTimeTo)", $sql);
        $em = $this->getEntityManager();

        $conn = $em->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        return $logs;
    }




    public function getLog(int $requestId)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT  l.millis, g.latitude, g.longitude, g.altitude, g.speed, g.course, g.gpsSatellites, g.gnssSatellitesUsed, g.glonass, g.CN, g.millis AS millisGPS
                FROM log l INNER JOIN gps g ON l.id = g.log_id WHERE l.requestId_id = :request_id ORDER BY l.id
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['request_id' => $requestId]);

        return $stmt->fetchAll();
    }

    /**
     * Возвращает логи  по заданной GPS дате
     * @param \DateTime $dateTime
     * @return Line []
     */
    public function findByDateTime(\DateTime $dateTime)
    {
        $dateTimeTo = clone $dateTime;
        $dateTimeTo->modify('+1 day');
        $querySQL = "  SELECT
    l.millis,
    g.datetime, g.latitude, g.longitude, g.altitude, g.speed, g.course, g.gpsSatellites, g.gnssSatellitesUsed, g.glonass, g.CN, g.millis as GPS_millis, 
    m.rssi, m.ber, m.MemoryFree, m.millis AS CSQ_millis
    FROM log l 
    INNER JOIN gps g ON l.id = g.log_id 
    INNER JOIN modem m ON l.id = m.log_id
  WHERE g.datetime BETWEEN :start AND :finish GROUP BY g.datetime";
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($querySQL);
        $stmt->execute([
            'start' => $dateTime->format("Y-m-d"),
            'finish' => $dateTimeTo->format("Y-m-d")

        ]);
        $logs = $stmt->fetchAll();



        return $logs;
    }

    /**
     * @param \DateTime|null $dateTime
     * @return Line []
     * @throws \Exception
     */
    public function findByDateTime2(string $imei, \DateTime $dateTime = null)
    {

        if ($dateTime === null)
            $dateTime = new \DateTime();
        $dateTime->setTime(0, 0, 0);
        $dateTo = clone $dateTime;
        $dateTo->setTime(23, 59, 59);


        $qb = $this->createQueryBuilder('l')
            ->addSelect('g')
            ->addSelect('r')
            ->addSelect('m')
            ->addSelect('m1')
            ->leftJoin('l.requestId', 'r')
            ->innerJoin('l.gps', 'g')
            ->innerJoin('l.message', 'm')
            ->innerJoin('l.modem', 'm1')
            ->where("r.imei LIKE :imei")
            ->setParameter("imei", $imei . "%")
            ->andWhere('r.datetime BETWEEN  :from AND :to')
            ->setParameter('from', $dateTime)
            ->setParameter('to', $dateTo)
            ->orderBy('r.id', 'ASC');
        $query = $qb->getQuery();
        $logs = $query->getResult();


        return $logs;
    }

    /**
     * @param int $truckId
     * @param \DateTime $dateTimeFrom
     * @param \DateTime $dateTimeTo
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByTruck(int $truckId, \DateTime $dateTimeFrom, \DateTime $dateTimeTo): array
    {
        $sql = $this->getSQL();
        $params = [
            'truck_id' => $truckId,
            'dateTimeFrom' => $dateTimeFrom->format('Y-m-d H:i:s'),
            'dateTimeTo' => $dateTimeTo->format('Y-m-d H:i:s'),
        ];
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        return $logs;
    }

    /**
     * @param $imei
     * @param $logId
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByLogMoreThan($logId, \DateTime $dateTimeFrom, \DateTime $dateTimeTo)
    {
        $sql = $this->getSQL(true); // TODO: Сделать ограничение по дате
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'log_id' => $logId,
            'dateTimeFrom' => $dateTimeFrom->format('Y-m-d H:i:s'),
            'dateTimeTo' => $dateTimeTo->format('Y-m-d H:i:s'),
        ]);
        $logs = $stmt->fetchAll();

        return $logs;
    }

    /**
     * @param $logId
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByLog($logId)
    {
        $sql = $this->getSQL() . "AND l.id = :id";
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'id' => $logId
        ]);
        $logs = $stmt->fetchAll();

        return $logs;
    }


    /**
     * @param $sql
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute($sql)
    {
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

}
