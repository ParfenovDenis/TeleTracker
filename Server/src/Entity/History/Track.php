<?php
/**
 * @license AVT
 */

namespace App\Entity\History;


use App\Entity\Module\GPS;
use App\Entity\Routing\Route;
use App\Model\Analytics\Truck\Traffic;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TrackHistoryRepository")
 * Class Track
 * @package App\Entity\History
 */
class Track
{


    /**
     * @var array
     */
    private $waypoints = [];



    /**
     * @var float
     */
    private $minLatitude = 0.00;

    /**
     * @var float
     */
    private $maxLatitude = 0.00;
    /**
     * @var float
     */
    private $minLongitude = 0.00;
    /**
     * @var float
     */
    private $maxLongitude = 0.00;

    /**
     * @var array
     */
    private $thead = [];

    /**
     * @var array
     */
    private $parking = [];

    /**
     * @var \DateInterval
     */
    private $PARKING_INTERVAL;

    /**
     * @var array
     */
    private $refueling = [];

    /**
     * @var float
     */
    private $totalFuelEconomy = 0.00;

    /**
     * @var int
     */
    private $cntFuelEconomy = 0;

    /**
     * @var float
     */
    private $startFuel = 0.00;

    /**
     * @var int
     */
    private $startDistance = 0;

    /**
     * @var int
     */
    private $currentDistance = 0;
    /**
     * @var GPS
     */
    private $lastGPS;

    /**
     * @var Traffic
     */
    private $traffic;

    /**
     * @var float
     */
    private $currentFuel = 0.00;

    /**
     * @var int
     */
    private $penalty;

    /**
     * @var array
     */
    private $penaltyLogs;

    /**
     * @var array
     */
    private $maxAccelerationLog = [];

    /**
     * @var array
     */
    private $maxBrakingLog = [];

    /**
     * @var Route
     */
    private $route;

    /**
     * Track constructor.
     */
    public function __construct()
    {
        $this->PARKING_INTERVAL = new \DateInterval("PT3M");
        $this->PARKING_INTERVAL->invert = 1;
        $this->PARKING_INTERVAL->days = 0;
    }

    /**
     * @return \DateInterval
     */
    public function getPARKINGINTERVAL(): \DateInterval
    {
        return $this->PARKING_INTERVAL;
    }

    /**
     * @param \DateInterval $dateInterval
     * @return int
     */
    public function compareParkingInterval(\DateInterval $dateInterval)
    {
        if ($dateInterval->h > $this->PARKING_INTERVAL->h)
            return 1;
        if ($dateInterval->h < $this->PARKING_INTERVAL->h)
            return -1;
        if ($dateInterval->i > $this->PARKING_INTERVAL->i)
            return 1;
        if ($dateInterval->i < $this->PARKING_INTERVAL->i)
            return -1;
        if ($dateInterval->s > $this->PARKING_INTERVAL->s)
            return 1;
        if ($dateInterval->s < $this->PARKING_INTERVAL->s)
            return -1;

        return 0;
    }

    /**
     * @return array
     */
    public function getWaypoints(): array
    {
        return $this->waypoints;
    }

    public function clearWaypoints() {
        $this->waypoints = [];
        return $this;
    }

    /**
     * @param array $waypoints
     * @return Track
     */
    public function setWaypoints(array $waypoints): Track
    {
        $this->waypoints = $waypoints;
        return $this;
    }


    public function clearDestinations() {
        $this->destinations = [];
        return $this;
    }



    /**
     * @return float
     */
    public function getMinLatitude(): float
    {
        return $this->minLatitude;
    }

    /**
     * @param float $minLatitude
     * @return Track
     */
    public function setMinLatitude(float $minLatitude): Track
    {
        $this->minLatitude = $minLatitude;
        return $this;
    }

    /**
     * @return float
     */
    public function getMaxLatitude(): float
    {
        return $this->maxLatitude;
    }

    /**
     * @param float $maxLatitude
     * @return Track
     */
    public function setMaxLatitude(float $maxLatitude): Track
    {
        $this->maxLatitude = $maxLatitude;
        return $this;
    }

    /**
     * @return float
     */
    public function getMinLongitude(): float
    {
        return $this->minLongitude;
    }

    /**
     * @param float $minLongitude
     * @return Track
     */
    public function setMinLongitude(float $minLongitude): Track
    {
        $this->minLongitude = $minLongitude;
        return $this;
    }

    /**
     * @return float
     */
    public function getMaxLongitude(): float
    {
        return $this->maxLongitude;
    }

    /**
     * @param float $maxLongitude
     * @return Track
     */
    public function setMaxLongitude(float $maxLongitude): Track
    {
        $this->maxLongitude = $maxLongitude;
        return $this;
    }

    /**
     * @param array $thead
     * @return Track
     */
    public function setThead(array $thead): Track
    {
        $this->thead = $thead;
        return $this;
    }

    /**
     * @return array
     */
    public function getThead(): array
    {
        return $this->thead;
    }

    /**
     * @param array $parking
     * @return Track
     */
    public function setParking(array $parking): Track
    {
        $this->parking = $parking;
        return $this;
    }

    /**
     * @return array
     */
    public function getParking(): array
    {
        return $this->parking;
    }

    /**
     * @param array $refueling
     * @return Track
     */
    public function setRefueling(array $refueling): Track
    {
        $this->refueling = $refueling;
        return $this;
    }

    /**
     * @return array
     */
    public function getRefueling(): array
    {
        return $this->refueling;
    }

    /**
     * @return float
     */
    public function getTotalFuelEconomy(): float
    {
        return $this->totalFuelEconomy;
    }

    /**
     * @param float $totalFuelEconomy
     * @return Track
     */
    public function setTotalFuelEconomy(float $totalFuelEconomy): Track
    {
        $this->totalFuelEconomy = $totalFuelEconomy;
        return $this;
    }

    /**
     * @return int
     */
    public function getCntFuelEconomy(): int
    {
        return $this->cntFuelEconomy;
    }

    /**
     * @param int $cntFuelEconomy
     * @return Track
     */
    public function setCntFuelEconomy(int $cntFuelEconomy): Track
    {
        $this->cntFuelEconomy = $cntFuelEconomy;
        return $this;
    }

    /**
     * @return float
     */
    public function getStartFuel(): float
    {
        return $this->startFuel;
    }

    /**
     * @param float $startFuel
     * @return Track
     */
    public function setStartFuel(float $startFuel): Track
    {
        $this->startFuel = $startFuel;
        return $this;
    }

    /**
     * @return int
     */
    public function getStartDistance(): int
    {
        return $this->startDistance;
    }

    /**
     * @param int $startDistance
     * @return Track
     */
    public function setStartDistance(int $startDistance): Track
    {
        $this->startDistance = $startDistance;
        return $this;
    }

    /**
     * @return GPS
     */
    public function getLastGPS(): ?GPS
    {
        return $this->lastGPS;
    }

    /**
     * @param GPS $lastGPS
     * @return Track
     */
    public function setLastGPS(?GPS $lastGPS): Track
    {
        $this->lastGPS = $lastGPS;
        return $this;
    }

    /**
     * @return Traffic
     */
    public function getTraffic(): ?Traffic
    {
        return $this->traffic;
    }

    /**
     * @param Traffic $traffic
     * @return Track
     */
    public function setTraffic(Traffic $traffic): Track
    {
        $this->traffic = $traffic;
        return $this;
    }

    /**
     * @return float
     */
    public function getCurrentFuel(): float
    {
        return $this->currentFuel;
    }

    /**
     * @param float $currentFuel
     * @return Track
     */
    public function setCurrentFuel(float $currentFuel): Track
    {
        $this->currentFuel = $currentFuel;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentDistance(): int
    {
        return $this->currentDistance;
    }

    /**
     * @param int $currentDistance
     * @return Track
     */
    public function setCurrentDistance(int $currentDistance): Track
    {
        $this->currentDistance = $currentDistance;
        return $this;
    }

    /**
     * @return int
     */
    public function getPenalty(): int
    {
        return $this->penalty;
    }

    /**
     * @param int $penalty
     * @return Track
     */
    public function setPenalty(int $penalty): Track
    {
        $this->penalty = $penalty;
        return $this;
    }

    /**
     * @return array
     */
    public function getPenaltyLogs(): array
    {
        return $this->penaltyLogs;
    }

    /**
     * @param array $penaltyLogs
     * @return Track
     */
    public function setPenaltyLogs(array $penaltyLogs): Track
    {
        $this->penaltyLogs = $penaltyLogs;
        return $this;
    }

    /**
     * @return array
     */
    public function getMaxAccelerationLog(): array
    {
        return $this->maxAccelerationLog;
    }

    /**
     * @param array $maxAccelerationLog
     * @return Track
     */
    public function setMaxAccelerationLog(array $maxAccelerationLog): Track
    {
        $this->maxAccelerationLog = $maxAccelerationLog;
        return $this;
    }

    /**
     * @return array
     */
    public function getMaxBrakingLog(): array
    {
        return $this->maxBrakingLog;
    }

    /**
     * @param array $maxBrakingLog
     * @return Track
     */
    public function setMaxBrakingLog(array $maxBrakingLog): Track
    {
        $this->maxBrakingLog = $maxBrakingLog;
        return $this;
    }

    /**
     * @return Route
     */
    public function getRoute(): ?Route
    {
        return $this->route;
    }

    /**
     * @param Route $route
     * @return Track
     */
    public function setRoute(?Route $route): Track
    {
        $this->route = $route;
        return $this;
    }



}
