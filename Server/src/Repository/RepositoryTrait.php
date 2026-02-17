<?php
/**
 * Copyright (c) 2019.
 */

namespace App\Repository;


use Doctrine\ORM\EntityManager;

trait RepositoryTrait
{
    public function setEntityManager(EntityManager $em)
    {
        $this->_em = $em;
    }
}