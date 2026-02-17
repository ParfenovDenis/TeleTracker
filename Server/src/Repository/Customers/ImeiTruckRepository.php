<?php

namespace App\Repository\Customers;

use App\Entity\Customers\ImeiTruck;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ImeiTruck|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImeiTruck|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImeiTruck[]    findAll()
 * @method ImeiTruck[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImeiTruckRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImeiTruck::class);
    }

    /**
     * @param int $truckId
     * @param \DateTime $dateTimeFrom
     * @param \DateTime $dateTimeTo
     * @return ImeiTruck[]
     */
    public function findByTruckAndDateTime(?int $truckId, \DateTime $dateTimeFrom, \DateTime $dateTimeTo)
    {
        $entityManager = $this->getEntityManager();
        $dql = 'SELECT i
            FROM App\Entity\Customers\ImeiTruck i
            WHERE ';
        if (!is_null($truckId))
            $dql .= ' i.truck = :truck AND ';
        $dql .= 'i.DateTimeFrom <= :dateTimeTo AND
            (i.DateTimeTo >= :dateTimeFrom OR i.DateTimeTo IS NULL)';
        $query = $entityManager->createQuery(
            $dql
        )
            ->setParameter('dateTimeTo', $dateTimeTo)
            ->setParameter('dateTimeFrom', $dateTimeFrom);
        if (!is_null($truckId))
            $query->setParameter('truck', $truckId);
        /** @var ImeiTruck[] $imeiTrucks */
        $imeiTrucks = $query->getResult();
        foreach ($imeiTrucks as $imeiTruck) {
            if ($imeiTruck->getDateTimeFrom() < $dateTimeFrom)
                $imeiTruck->setDateTimeFrom($dateTimeFrom);
            if (is_null($imeiTruck->getDateTimeTo()) || $imeiTruck->getDateTimeTo() > $dateTimeTo)
                $imeiTruck->setDateTimeTo($dateTimeTo);
        }

        return $imeiTrucks;
    }


}
