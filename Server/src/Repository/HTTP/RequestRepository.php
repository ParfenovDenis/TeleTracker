<?php
/**
 * Copyright (c) 2019.
 */

namespace App\Repository\HTTP;

use App\Entity\HTTP\Request;
use App\Repository\RepositoryInterface;
use App\Repository\RepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
//use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Request|null find($id, $lockMode = null, $lockVersion = null)
 * @method Request|null findOneBy(array $criteria, array $orderBy = null)
 * @method Request[]    findAll()
 * @method Request[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestRepository extends ServiceEntityRepository implements RepositoryInterface
{
    use RepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Request::class);
    }

    /**
     * @return Request []
     * @throws \Exception
     */
    public function findByDate($imei = null, $from = null, $to = null)
    {
        $day = date("d");
        $month = date("m");
        $year = date("Y");
        if (!$from)
            $from = new \DateTime($year."-" . $month . "-" . $day . " 00:00:00");
        if (!$to)
            $to = new \DateTime($year . "-" . $month . "-" . $day . " 23:59:59");
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.datetime BETWEEN  :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to);
        if ($imei)
            $qb->andWhere('r.imei LIKE :imei')->setParameter('imei', $imei);

        $requests = $qb->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $requests;
    }

    /**
     * @return Request []
     */
    public function getRawRequests()
    {
        $query = $this->createQueryBuilder('r')

            ->where('r.isProcessed = 0')
            ->andWhere('LENGTH(r.content) > 0')
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(100)
            ->getQuery()
            ;
        $sql = $query->getSQL();
        $requests = $query->getResult();
        return $requests;
    }

    /**
     * @param $requestId
     * @return Request
     */
    public function getNextRequest(int $requestId, string $imei): ?Request
    {
        $requests = $this->createQueryBuilder('r')
            ->where('r.id > :request')
            ->setParameter('request', $requestId)
            ->andWhere('r.imei LIKE :imei')
            ->setParameter('imei', $imei)
            ->setMaxResults(1)
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();

        if (is_array($requests) && \count($requests) > 0)
            return $requests[0];
        else
            return null;
    }

    /**
     * @param $requestId
     * @return Request
     */
    public function getPreviousRequest(int $requestId, string $imei): ?Request
    {
        $requests = $this->createQueryBuilder('r')
            ->where('r.id < :request')
            ->setParameter('request', $requestId)
            ->andWhere('r.imei LIKE :imei')
            ->setParameter('imei', $imei)
            ->setMaxResults(1)
            ->orderBy('r.id', 'DESC')
            ->getQuery()
            ->getResult();

        if (is_array($requests) && \count($requests) > 0)
            return $requests[0];
        else
            return null;
    }

    public function getEntityManager()
    {
        return parent::getEntityManager();
    }





}
