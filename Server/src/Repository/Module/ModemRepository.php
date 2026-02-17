<?php

namespace App\Repository\Module;

use App\Entity\Module\Modem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Modem|null find($id, $lockMode = null, $lockVersion = null)
 * @method Modem|null findOneBy(array $criteria, array $orderBy = null)
 * @method Modem[]    findAll()
 * @method Modem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Modem::class);
    }


}
