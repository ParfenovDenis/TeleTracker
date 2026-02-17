<?php
/**
 * Copyright (c) 2021.
 */

namespace App\Model\Analytics\Truck;


use App\Entity\CanBus\Message;
use App\Entity\Customers\Truck;
use App\Entity\History\Track;
use App\Entity\Module\GPS;

class State
{
    /**
     * @var Truck
     */
    private $truck;


    /**
     * @var Track
     */
    private $track;

    /**
     * @var float
     */
    private $fueled = 0.00;


    /**
     * State constructor.
     * @param Truck $truck
     */
    public function __construct(Truck $truck, Track $track)
    {
        $this->truck = $truck;
        $this->track = $track;
        $refueling = $track->getRefueling();
        foreach ($refueling as $ref) {
            $this->fueled += (float)$ref['fueled'];
        }


    }

    public function getJsonState(): JsonState
    {
        $state = new JsonState();
        $state->truckId = $this->truck->getId();
        $state->latitude = $this->getGPS() ? $this->getGPS()->getLatitude() : null;
        $state->longitude = $this->getGPS() ? $this->getGPS()->getLongitude() : null;
        $state->trafficImg = $this->getTrafficState()->getStatus();
        $state->trafficDesc = $this->getTrafficState()->getDescription();
        $state->Fuel = $this->getFuel();
        $state->Distance = $this->getDistance();
        $state->FuelConsumptionTank = $this->getFuelConsumptionTank();
        $state->AverageFuelConsumption = $this->getAverageFuelConsumption();
        $state->DistancePerDay = $this->getDistancePerDay();
        $state->FuelEconomy = $this->getFuelEconomy();
        $state->AverageFuelEconomy = $this->getAverageFuelEconomy();
        $state->ParkingQty = $this->getParkingQty();
        $state->ParkingInterval = $this->getParkingDateInterval()->format("%H:%I");
        $state->PenaltyTotal = $this->getPenaltyTotal();
        $state->PenaltyLogs = $this->getPenaltyLogs();

        return $state;
    }

    /**
     * @return Truck
     */
    public function getTruck(): Truck
    {
        return $this->truck;
    }

    public function getFuel(): float
    {
        return round($this->truck->getFuelTank() * $this->track->getCurrentFuel() / 100, 2);

    }

    public function getFuelConsumptionTank(): float
    {
        return ($value = round($this->truck->getFuelTank() * ($this->track->getStartFuel() + $this->fueled - $this->track->getCurrentFuel()) / 100, 2))>0?$value:0.00;
    }

    public function getDistance(): int
    {
        return $this->track->getCurrentDistance();
    }

    public function getDistancePerDay(): int
    {
        return $this->getDistance() - $this->track->getStartDistance();
    }

    public function getAverageFuelConsumption(): ?float
    {
        return $this->track->getCntFuelEconomy() > 0 ? round($this->track->getTotalFuelEconomy() / $this->track->getCntFuelEconomy() *
            $this->track->getCntFuelEconomy() / 3600, 2) : null;
    }

    public function getFuelEconomy(): float
    {
        return $this->getDistancePerDay() > 0 ? round(($this->getFuelConsumptionTank() / $this->getDistancePerDay() * 100), 2) : 0.00;
    }

    public function getAverageFuelEconomy(): ?float
    {
        return ($this->track->getCntFuelEconomy() >0 && $this->getDistancePerDay()  > 0 )?
            round($this->track->getTotalFuelEconomy() /
                $this->track->getCntFuelEconomy() *
            $this->track->getCntFuelEconomy() / 3600 /
                ($this->getDistancePerDay() / 100), 2) : null;
    }

    /**
     * @return GPS
     */
    public function getGPS(): ?GPS
    {
        return $this->track->getLastGPS();
    }

    public function getTrafficState(): Traffic
    {
        return $this->track->getTraffic();
    }

    public function getParkingQty(): int
    {
        $parking = $this->track->getParking();
        $qty = 0;
        foreach ($parking as $p)
            $qty++;
        return $qty;
    }

    public function getParkingDateInterval(): \DateInterval
    {
        $parking = $this->track->getParking();
        $di = new \DateInterval("P0D");
        foreach ($parking as $p) {
            $di = $this->sumDateInterval($di, $p['duration']);
        }
        return $di;
    }

    /**
     * @return int
     */
    public function getPenalty(): int
    {
        return $this->track->getPenalty();
    }

    public function getPenaltyLogs(): array
    {
        return $this->track->getPenaltyLogs();
    }

    public function getPenaltyTotal(): int
    {
        $penaltyLogs = $this->track->getPenaltyLogs();
        return count($penaltyLogs['acceleration']['error']) + count($penaltyLogs['acceleration']['warning']) + count($penaltyLogs['acceleration']['notice'])
            + count($penaltyLogs['braking']['error']) + count($penaltyLogs['braking']['warning']) + count($penaltyLogs['braking']['notice']);
    }

    private function sumDateInterval(\DateInterval $di1, \DateInterval $di2): \DateInterval
    {
        $di1->s += $di2->s;
        $di1->i += $di2->i;
        $di1->h += $di2->h;
        $di1->i += (int)($di1->s / 60);
        $di1->s = $di1->s % 60;
        $di1->h += (int)($di1->i / 60);
        $di1->i = $di1->i % 60;
        return $di1;
    }
}