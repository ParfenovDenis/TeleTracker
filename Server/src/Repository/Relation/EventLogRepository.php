<?php
/**
 * @license AVT
 */

namespace App\Repository\Relation;

use App\Entity\Relation\EventLog;
use App\Entity\Report\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EventLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventLog[]    findAll()
 * @method EventLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventLog::class);
    }

    /**
     * @param array $imei
     * @return EventLog[]
     */
    public function getLastLogs(array $imei)
    {
        $dql = "SELECT l FROM App\Entity\Relation\EventLog l WHERE l.imei IN (:imei)";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('imei', $imei);
        /**
         * @var EventLog[] $_lastLogs
         */
        $_lastLogs = $query->getResult();
        $lastLogs = [];
        foreach ($_lastLogs as $log) {
            $lastLogs[$log->getImei()] = $log;
        }

        return $lastLogs;
    }

    public function findLastState(string $imei, \DateTimeInterface $dateTime, Event $event):?EventLog
    {
        $dql = "SELECT l FROM App\Entity\Relation\EventLog l WHERE l.imei LIKE :imei AND l.event = :event AND l.firstDateTime BETWEEN :first_date_time AND :last_date_time";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('imei', $imei);
        $query->setParameter('event', Event::LAST_STATE_ID);
        $query->setParameter('first_date_time', $dateTime->format("Y-m-d 00:00:00"));
        $query->setParameter('last_date_time', $dateTime->format("Y-m-d 23:59:59"));
        $eventLog = null;
        try {
            $eventLog = $query->getSingleResult();
        } catch (NonUniqueResultException $exception) {
            $result = $query->getResult();
            $eventLog = $result[0];
        } catch (NoResultException $exception) {
        }
        return $eventLog;
    }

}
