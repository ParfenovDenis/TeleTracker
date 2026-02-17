<?php
/**
 * @license AVT
 */
namespace App\Repository\Customers;

use App\Entity\Customers\GroupCompanies;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GroupCompanies|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupCompanies|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupCompanies[]    findAll()
 * @method GroupCompanies[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupCompaniesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupCompanies::class);
    }


}
