<?php
/*
 * Copyright (c) 2024.
 */

namespace App\Doctrine\Persister;

use App\Entity\CanBus\Log\Line;
use App\Entity\CanBus\Message;
use App\Entity\Module\GPS;
use App\Entity\Module\Modem;
use App\Tests\Doctrine\Persister\PersisterTest;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class EntityPersister extends AbstractPersister
{


    public function persistLog(Line $line): PersisterInterface
    {

        $this->persistMessage($line->getMessage());
        $this->persistGps($line->getGps());
        $this->persistModem($line->getModem());
        $this->em->persist($line);

        return $this;
    }

    protected function persistMessage(?Message $message): void
    {
        if ($message)
            $this->em->persist($message);
    }

    protected function persistGps(?GPS $gps): void
    {
        if ($gps)
            $this->em->persist($gps);
    }

    protected function persistModem(?Modem $modem): void
    {
        if ($modem)
            $this->em->persist($modem);
    }

    public function flush(): bool
    {
        $this->em->flush();

        return true;
    }
}
