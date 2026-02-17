<?php
/*
 * Copyright (c) 2022.
 */

namespace App\Manager;


use App\Entity\Customers\Driver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class DriverManager extends AbstractBaseManager
{
    /**
     * DriverManager constructor.
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
        return $this->entityManager->getRepository(Driver::class);
    }

}