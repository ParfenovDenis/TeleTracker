<?php
/**
 * Copyright (c) 2019.
 */

namespace App\Doctrine;


use App\Entity\CanBus\Log\Line;
use App\Repository\CanBus\LogRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;

class LogIdGenerator extends AbstractIdGenerator
{
    /**
     * @param EntityManager $em
     * @param object|null $entity
     * @return mixed|void
     * @throws \Exception
     */
    public function generate(EntityManager $em, $entity)
    {
        /**
         * @var LogRepository $logRepository
         */
        $logRepository = $em->getRepository(Line::class);
        try {
            $logRepository->getNextId($entity);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

    }

}