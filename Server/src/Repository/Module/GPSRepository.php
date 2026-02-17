<?php
/**
 * @license AVT
 */
namespace App\Repository\Module;

use App\Entity\Module\GPS;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GPS|null find($id, $lockMode = null, $lockVersion = null)
 * @method GPS|null findOneBy(array $criteria, array $orderBy = null)
 * @method GPS[]    findAll()
 * @method GPS[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GPSRepository extends ServiceEntityRepository
{
    const LIMIT_ROWS  = 100;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GPS::class);
    }

    public function getGPSgroupByDay()
    {
        $gps = $this->createQueryBuilder('g')
            ->select('g.datetime, DAY(g.datetime) AS gDay, MONTH(g.datetime) AS gMonth, YEAR(g.datetime) AS gYear')
            ->groupBy('gYear')
            ->addGroupBy('gMonth')
            ->addGroupBy('gDay')
            ->orderBy('g.datetime')
            ->getQuery()
            ->getResult();

        return $gps;
    }

    /**
     * @param \DateTime $dateTime
     * @return GPS []
     */
    public function findByDateTime(\DateTime $dateTime)
    {
        $dateTimeTo = clone $dateTime;


        $dateTimeTo->modify('+1 day');
        $gps = $this->createQueryBuilder('g')->where('g.datetime BETWEEN :from AND :to')
            ->setParameter('from',$dateTime->format("Y-m-d"))
            ->setParameter('to',$dateTimeTo->format("Y-m-d"))
            ->groupBy('g.datetime')
            ->orderBy('g.datetime')
            ->getQuery()
            ->getResult();

        return $gps;
    }



}
