<?php

namespace App\Repository\Relation;

use App\Entity\Relation\CompanyContragent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompanyContragent|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyContragent|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyContragent[]    findAll()
 * @method CompanyContragent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyContragentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyContragent::class);
    }


}
