<?php
/**
 * Copyright (c) 2020.
 */

namespace App\Repository;


use App\Entity\History\Track;
use App\Entity\Module\GPS;
use App\Model\Analytics\Truck\Traffic;
use App\Repository\CanBus\LogRepository;
use App\Repository\Routing\RouteRepository;

use Doctrine\ORM\EntityManager;

class TrackHistoryRepository implements RepositoryInterface
{


    const MAX_DISTANCE_PER_SECOND = 0.000673; // 150 km/h 42 m/s
    const MIN_DISTANCE_PER_SECOND = 0.000001; // 150 km/h 42 m/s


    const MIN_INCREASE_REFUELING = 10;

    const MIN_NUMBER_WAYPOINTS = 5;
    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * @var RouteRepository
     */
    private $routeRepository;

    /**
     * TrackHistoryRepository constructor.
     */
    public function __construct(LogRepository $logRepository, RouteRepository $routeRepository)
    {
        $this->logRepository = $logRepository;
        $this->routeRepository = $routeRepository;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->logRepository->setEntityManager($em);

    }








}