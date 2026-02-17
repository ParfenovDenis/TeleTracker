<?php
/**
 * @license AVT
 */

namespace App\Repository\Customers;

use App\Entity\Customers\Truck;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Truck|null find($id, $lockMode = null, $lockVersion = null)
 * @method Truck|null findOneBy(array $criteria, array $orderBy = null)
 * @method Truck[]    findAll()
 * @method Truck[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TruckRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Truck::class);
    }

    /**
     * @param null $company
     * @return Truck[]
     */
    public function findMyTrucks($company = null)
    {
        $_trucks = ($company ? $this->findBy(['company' => $company]) : $this->findAll());
        $trucks = [];
        foreach ($_trucks as $truck) {
            $trucks[$truck->getId()] = $truck;
        }

        return $trucks;
    }


}
