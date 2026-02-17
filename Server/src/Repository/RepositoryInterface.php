<?php
/**
 * Copyright (c) 2020.
 */

namespace App\Repository;


use Doctrine\ORM\EntityManager;

interface RepositoryInterface
{
    public function setEntityManager(EntityManager $em);
}