<?php

namespace App\Repository\Routing;

use App\Entity\Routing\Waypoint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Waypoint|null find($id, $lockMode = null, $lockVersion = null)
 * @method Waypoint|null findOneBy(array $criteria, array $orderBy = null)
 * @method Waypoint[]    findAll()
 * @method Waypoint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WaypointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Waypoint::class);
    }

    /**
     * @param int       $route
     * @param \DateTime $dateTime
     *
     * @return int|mixed|string
     */
    public function findByRoute(int $route, \DateTime  $dateTime)
    {
        $from = $dateTime;
        $from->setTime(0, 0, 0);
        $to = clone $dateTime;
        $to->setTime(23, 59, 59);

        return $this->createQueryBuilder('w')
            ->andWhere('w.dateTime BETWEEN :from AND :to')
            ->andWhere('w.route = :route')
            ->setParameter('from', $from->format("Y-m-d H:i:s"))
            ->setParameter('to', $to->format("Y-m-d H:i:s"))
            ->setParameter('route', $route)
            ->getQuery()
            ->getResult();
    }


}
