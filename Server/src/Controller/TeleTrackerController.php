<?php
/**
 * Copyright (c) 2019.
 */

namespace App\Controller;


use App\Entity\CanBus\Log\Line;

use App\Entity\History\Track;
use App\Entity\Module\GPS;
use App\Entity\Module\Modem;
use App\Entity\HTTP\Request;


use App\Model\AbstractObjectBuilder;
use App\Model\Analytics\Truck\State;

use App\Model\Analytics\Truck\Traffic;
use App\Model\Converter;

use App\Model\ObjectBuilder;
use App\Model\ObjectBuilderV2;
use App\Model\ObjectBuilderV3;
use App\Model\ObjectBuilderV4;


use App\Repository\Customers\TruckRepository;
use App\Repository\Module\GPSRepository;

use App\Repository\RepositoryInterface;
use App\Repository\TrackHistoryRepository;


use App\Repository\CanBus\LogRepository;





use Doctrine\DBAL\DBALException;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request as HTTPRequest;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Stopwatch\Stopwatch;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;




/**
 * Class TeleTrackerController
 * @package App\Controller
 *
 */
class TeleTrackerController extends AbstractController
{
    const TEMPLATE_HISTORY = 'tele_tracker/history.html.twig';
    const TEMPLATE_GPS = 'tele_tracker/history_gps.html.twig';
    const TEMPLATE_MAP_LOG = 'tele_tracker/map_log.html.twig';

    const FIRMWARE_PATH = '/var/downloads/firmware/firmware.bin';
    const TEMPLATE_MAP = 'tele_tracker/map.osm.html.twig';
    const TEMPLATE_TABLE = 'tele_tracker/table.html.twig';
    const TEMPLATE_OVERVIEW = 'tele_tracker/overview.osm.html.twig';
    const TEMPLATE_PENALTY = 'tele_tracker/penalty.html.twig';

    const URL_TILE_SERVER = 'http://tt.avt-daf.ru:88/tiles/{z}/{x}/{y}.png';
    const TRUCKS = [
        '867556040597919' => 'Iveco Daily 863',
        '867556040598859' => 'Iveco Daily 318',
        '867556040598909' => 'Iveco Daily 755',
        '867556040597851' => 'Skoda Rapid',
        /*
                '8656040598859' => '8656040598859',
                '86816869471848' => '86816869471848',
                '86560405988591' => '86560405988591',
                '86816869472481' => '86816869472481',
                '8656040597919' => '8656040597919',
                '86816870411702' => '86816870411702',
                '86816869471604' => '86816869471604',*/
    ];
    use NavTrait;

    const MAX_DISTANCE_PER_SECOND = 0.000673; // 150 km/h 42 m/s

    protected $managerRegistry;

    /**
     * TeleTrackerController constructor.
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }


    /**
     * @Route ("/log/{imei}", name="log")
     * @return Response
     * @throws \Exception
     */
    public function log(HTTPRequest $httpRequest, $imei = null)
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $filePath = $projectDir . '/var/';
        if ($imei)
            $filePath .= $imei . '.log';
        else
            $filePath .= 'arduino.log';
        $h = fopen($filePath, "wb");
        fwrite($h, $httpRequest->getContent());
        fclose($h);

        return new Response();
    }

    /**
     * @Route ("/vin/low/{imei}", name="vin")
     * @param $imei
     * @return Response
     */
    public function vinLow($imei)
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $filePath = $projectDir . '/var/vin_low/' . $imei . '.log';
        try {
            $h = fopen($filePath, "at");
            $date = new \DateTime();
            fwrite($h, $date->format("Y-m-d H:i:s") . "\r\n");
            fclose($h);
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
        return new Response();
    }






    /**
     * @param null $day
     * @param null $month
     * @param null $year
     * @return \DateTime
     * @throws \Exception
     */
    private function getDateTimeFrom($day = null, $month = null, $year = null):\DateTime
    {
        if ($day && $month && $year)
            $dateFrom = new \DateTime($year . '-' . $month . '-' . $day);
        else
            $dateFrom = new \DateTime();
        $dateFrom->setTime(0, 0, 0);
        return $dateFrom;
    }

    /**
     * @param \DateTime $dateFrom
     * @param Track $historyTrack
     * @param TranslatorInterface $translator
     * @return array
     * @throws \Exception
     */
    private function getParams(\DateTime $dateFrom, Track $historyTrack, TranslatorInterface $translator)
    {
        $currentDate = new \DateTime();
        $currentDate->setTime(0, 0, 0);

        $version = time();
        $params = [
            'waypoints' => [],
            'params' => Line::PARAMS,
            'datetime' => $dateFrom/*->format("Y/m/d")*/,
            'current_datetime' => $dateFrom->format("d.m.Y"),
            'trucks' => static::TRUCKS,
            'version' => $version,
            'allowRefreshMap' => false,
            'route' => '/account/history/',
            'parking_points' => $historyTrack->getParking(),
            'refueling_points' => $historyTrack->getRefueling(),
            'current_fuel' => $historyTrack->getCurrentFuel(),
            'start_distance' => $historyTrack->getStartDistance(),
            'start_fuel' => $historyTrack->getStartFuel(),
            'total_fuel_economy' => $historyTrack->getTotalFuelEconomy(),
            'cnt_fuel_economy' => $historyTrack->getCntFuelEconomy(),
            'truck_route' => $historyTrack->getRoute(),
            'url_tile_server' => static::URL_TILE_SERVER

        ];


        $minLatitude = $historyTrack->getMinLatitude();
        $maxLatitude = $historyTrack->getMaxLatitude();
        $minLongitude = $historyTrack->getMinLongitude();
        $maxLongitude = $historyTrack->getMaxLongitude();
        $waypoints = $historyTrack->getWaypoints();

        $startThead = current($historyTrack->getThead()); // заголовки для столбцов таблицы  (расход топлива л/100км или л/ч )
        $params ['thead'] = [];
        if ($startThead) {
            foreach ($startThead as $th => $message)
                $params['thead'][$th] = $translator->trans($message);
        }
        $params['waypoints'] = $waypoints;
        $params['current_waypoint'] = reset($waypoints);

        if (count($waypoints) === 0)
            $params['no_traffic_recorded_on_this_day'] = true;
        $params['center'] = $this->getCenterMap($minLatitude, $maxLatitude, $minLongitude, $maxLongitude);
        if ($currentDate <= $dateFrom) {
            $params['allowRefreshMap'] = true;
            $params['current_waypoint'] = end($waypoints);
        }
        return $params;
    }

    private function getCenterMap($minLatitude, $maxLatitude, $minLongitude, $maxLongitude): array
    {
        return ['lat' => ($minLatitude + ($maxLatitude - $minLatitude) / 2), 'lng' => ($minLongitude + ($maxLongitude - $minLongitude) / 2)];
    }











    /**
     * @Route("/track/history/table/{truck}/{year}/{month}/{day}", name="track_history_table", requirements={"imei": "\d{10,15}", "day": "\d{1,2}","month": "\d{1,2}","year": "\d{4,4}"})
     * @IsGranted("ROLE_DEV")
     */
    public function table(LogRepository $logRepository, $truck = null, $day = null, $month = null, $year = null)
    {
        $stopwatch = new Stopwatch();

        $this->setCorrectEntityManager($logRepository);

        if ($day && $month && $year)
            $dateFrom = new \DateTime($year . '-' . $month . '-' . $day);
        else
            $dateFrom = new \DateTime();
        $dateFrom->setTime(0, 0, 0);
        $stopwatch->start('table');
        try {
            $logs = $logRepository->getLogs($dateFrom, $truck);
            return $this->render(self::TEMPLATE_TABLE,
                [
                    'lines' => $logs,
                    'trucks' => self::TRUCKS,
                ]);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), $e->getCode());
        }
    }













    protected function setCorrectEntityManager(RepositoryInterface $repository)
    {

        if ('dev' === $this->getParameter('kernel.environment')) {
            $repository->setEntityManager($this->managerRegistry->getManager('agava'));
        }

    }
}