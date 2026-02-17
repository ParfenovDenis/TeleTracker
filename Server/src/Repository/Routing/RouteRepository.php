<?php
/**
 * @license AVT
 */

namespace App\Repository\Routing;

use App\Entity\Routing\Route;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Route|null find($id, $lockMode = null, $lockVersion = null)
 * @method Route|null findOneBy(array $criteria, array $orderBy = null)
 * @method Route[]    findAll()
 * @method Route[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RouteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Route::class);
    }

    /**
     * @param int $truckId
     * @param \DateTime $dateTime
     * @return Route
     */
    public function getRoute(int $truckId, \DateTime $dateTime)
    {
        $route = $this->createQueryBuilder('r')
            ->andWhere('r.truck = :truck_id')
            ->andWhere('r.dateTime BETWEEN :from AND :to')
            ->setParameter('truck_id', $truckId)
            ->setParameter('from', $dateTime->format("Y-m-d 00:00:00"))
            ->setParameter('to', $dateTime->format("Y-m-d 23:59:59"))
            ->getQuery()
            ->getOneOrNullResult();
        return $route;
    }

    /**
     * @param \DateTime $dateTime
     * @return Route
     */
    public function getRoutes( \DateTime $dateTime)
    {
        $route = $this->createQueryBuilder('r')
            ->andWhere('r.dateTime BETWEEN :from AND :to')
            ->setParameter('from', $dateTime->format("Y-m-d 00:00:00"))
            ->setParameter('to', $dateTime->format("Y-m-d 23:59:59"))
            ->getQuery()
            ->getResult();
        return $route;
    }


}
