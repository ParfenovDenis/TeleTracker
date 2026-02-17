<?php
/*
 * Copyright (c) 2023.
 */

namespace App\Manager;



use App\Entity\Relation\DriverTruck;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class DriverTruckManager extends AbstractBaseManager
{
    /**
     * DriverTruckManager constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(DriverTruck::class);
    }
}