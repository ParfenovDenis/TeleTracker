<?php
/*
 * Copyright (c) 2024.
 */

namespace App\Doctrine\Persister;

use App\Entity\CanBus\Log\Line;
use App\Entity\CanBus\Message;
use App\Entity\HTTP\Request;
use App\Entity\Module\GPS;
use App\Entity\Module\Modem;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractPersister implements PersisterInterface
{


    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function flushRequest(Request $request): void
    {
        $this->em->persist($request);
        $this->em->flush();
    }


}