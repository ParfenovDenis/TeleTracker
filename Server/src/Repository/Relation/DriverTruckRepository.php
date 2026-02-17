<?php
/**
 * @license AVT
 */

namespace App\Repository\Relation;

use App\Entity\Customers\Driver;
use App\Entity\Customers\Truck;
use App\Entity\Relation\DriverTruck;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method DriverTruck|null find($id, $lockMode = null, $lockVersion = null)
 * @method DriverTruck|null findOneBy(array $criteria, array $orderBy = null)
 * @method DriverTruck[]    findAll()
 * @method DriverTruck[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DriverTruckRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DriverTruck::class);
    }


    /**
     * @param \DateTimeInterface $dateTimeFrom
     * @param \DateTime $dateTimeTo
     * @param Driver $driver
     * @param Truck $truck
     * @return DriverTruck[]
     */
    public function getConflictDriverTrucks(\DateTimeInterface $dateTimeFrom, \DateTimeInterface $dateTimeTo, Truck $truck)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT d
    FROM App\Entity\Relation\DriverTruck d
    WHERE  d.truck = :truck AND
  ((:dateTimeFrom BETWEEN d.dateTimeFrom AND d.dateTimeTo) OR 
  (:dateTimeFrom <= d.dateTimeFrom AND :dateTimeTo >= d.dateTimeFrom)  OR
   (:dateTimeTo BETWEEN d.dateTimeFrom AND d.dateTimeTo) OR
  (:dateTimeFrom >= d.dateTimeFrom AND d.dateTimeTo IS NULL ))'
        )->setParameter('dateTimeFrom', $dateTimeFrom)
            ->setParameter('dateTimeTo', $dateTimeTo)
            ->setParameter('truck', $truck);

        return $query->getResult();
    }
}
