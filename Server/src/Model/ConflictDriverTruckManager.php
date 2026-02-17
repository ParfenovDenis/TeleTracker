<?php
/*
 * Copyright (c) 2023.
 */

namespace App\Model;


use App\Entity\Relation\DriverTruck;
use App\Repository\Relation\DriverTruckRepository;
use Doctrine\ORM\EntityManagerInterface;

class ConflictDriverTruckManager
{

    private $repository;

    /**
     * @var DriverTruck[]
     */
    private $driverTrucks;

    /**
     * @var DriverTruck
     */
    private $driverTruck;

    private $entityManager;

    /**
     * ConflictDriverTruckManager constructor.
     */
    public function __construct(DriverTruckRepository $driverTruckRepository, EntityManagerInterface $entityManager)
    {
        $this->repository    = $driverTruckRepository;
        $this->entityManager = $entityManager;

    }

    /**
     * @param DriverTruckRepository $repository
     */
    public function setRepository(DriverTruckRepository $repository): void
    {
        $this->repository = $repository;
    }

    public function exist(DriverTruck $driverTruck = null): bool
    {
        if ($driverTruck) {
            $this->driverTruck = $driverTruck;
            $this->driverTrucks = $this->repository->getConflictDriverTrucks($driverTruck->getDateTimeFrom(),
                $driverTruck->getDateTimeTo() ?: new \DateTime(), $driverTruck->getTruck());
        }
        return count($this->driverTrucks) > 0;
    }

    /**
     * @return DriverTruck[]
     */
    public function getDriverTrucks(): array
    {
        return $this->driverTrucks;
    }

    /**
     * @return DriverTruck
     */
    public function getDriverTruck(): DriverTruck
    {
        return $this->driverTruck;
    }

    /**
     * @throws \Exception
     */
    public function resolve():void
    {
        $dateTimeFrom = $this->driverTruck->getDateTimeFrom();
        $dateTimeTo = $this->driverTruck->getDateTimeTo();
        if (count($this->driverTrucks) > 1)
            throw new \RuntimeException("Метод не протестирован на несколько conflictDriverTruck");
        foreach ($this->driverTrucks as $c => $conflictDriverTruck) {
            if ($this->isToFrom($this->driverTruck, $conflictDriverTruck)) {
                $conflictDriverTruck->setDateTimeTo($dateTimeFrom);
                $this->entityManager->persist($conflictDriverTruck);
                continue;
            }
            if ($this->isFromTo($this->driverTruck, $conflictDriverTruck))
            {
                $conflictDriverTruck->setDateTimeFrom($dateTimeTo);
                continue;
            }
            if ($this->isRemove($this->driverTruck, $conflictDriverTruck)) {
                $this->entityManager->remove($conflictDriverTruck);
                unset($this->driverTrucks[$c]);
            }
            $this->entityManager->flush();
        }
    }

    protected function isToFrom(DriverTruck $new, DriverTruck $exist): bool
    {
        $newFrom = $new->getDateTimeFrom();
        $existFrom = $exist->getDateTimeFrom();
        $newTo = $new->getDateTimeTo();
        $existTo = $exist->getDateTimeTo();
        if ($newFrom > $existFrom && $newTo === null && $existTo === null)
            return true;
        if ($newFrom > $existFrom && $newTo < $existTo)
            return true;
        if ($newFrom < $existTo && $newFrom > $existFrom && $newTo > $existTo)
            return true;

        return false;
    }

    protected function isFromTo(DriverTruck $new, DriverTruck $exist): bool
    {
        $newFrom = $new->getDateTimeFrom();
        $existFrom = $exist->getDateTimeFrom();
        $newTo = $new->getDateTimeTo();
        $existTo = $exist->getDateTimeTo();

        return ($newFrom < $existFrom && $newTo > $existFrom && $newTo < $existTo);
    }

    protected function isRemove(DriverTruck $new, DriverTruck $exist): bool
    {
        $newFrom = $new->getDateTimeFrom();
        $existFrom = $exist->getDateTimeFrom();
        $newTo = $new->getDateTimeTo();
        $existTo = $exist->getDateTimeTo();
        if ($newFrom < $existFrom) {
            if ($newTo === null && $existTo === null)
                return true;
            if ($newTo > $existTo)
                return true;
        }

        return false;
    }



}