<?php

namespace App\Repository\CanBus;

use App\Entity\CanBus\Offset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Offset|null find($id, $lockMode = null, $lockVersion = null)
 * @method Offset|null findOneBy(array $criteria, array $orderBy = null)
 * @method Offset[]    findAll()
 * @method Offset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OffsetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offset::class);
    }


}
