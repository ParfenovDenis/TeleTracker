<?php
/**
 * @license AVT
 */

namespace App\Controller;

use App\Entity\Customers\Company;

use App\Entity\Relation\CompanyContragent;
use App\Entity\Routing\Route as TruckRoute;

use App\Entity\Routing\Waypoint;
use App\Form\Routing\WaypointType;
use App\Repository\Logic\LogicRepository;
use App\Repository\Relation\CompanyContragentRepository;
use App\Repository\Routing\RouteRepository;
use App\Repository\Routing\WaypointRepository;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * @Route("/routing/waypoint")
 *
 * @IsGranted("ROLE_USER")
 */
class WaypointController extends AbstractController
{
    use NavTrait;


    /**
     * @param Request $request
     * @param LogicRepository $logicRepository
     * @param RouteRepository $routeRepository
     * @param int $routeId
     *
     * @return Response
     *
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     *
     * @Route("/new/{routeId}", name="waypoint_new", methods={"GET","POST"})
     */
    public function new(Request $request, LogicRepository $logicRepository, RouteRepository $routeRepository, int $routeId): Response
    {
        $waypoint = new Waypoint();
        $route = $routeRepository->find($routeId);
        $waypoint->setRoute($route);
        $form = $this->createForm(WaypointType::class, $waypoint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($waypoint);
            $entityManager->flush();

            return $this->redirectToRoute('route_waypoints', ['route' => $waypoint->getRoute()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('routing/waypoint/new.html.twig', [
            'waypoint' => $waypoint,
            'documents' => $logicRepository->getDocuments($route->getDateTime()),
            'form' => $form->createView(),
            'route' => $routeId,
            'location' => CompanyController::DEFAULT_LOCATION,
            'url_tile_server' => TeleTrackerController::URL_TILE_SERVER,
        ]);
    }

    /**
     * @Route("/{id}", name="waypoint_show", methods={"GET"}, requirements={"id"="\d+"})
     *
     * @param Waypoint $waypoint
     *
     * @return Response
     */
    public function show(Waypoint $waypoint): Response
    {
        return $this->render('routing/waypoint/show.html.twig', [
            'waypoint' => $waypoint,
        ]);
    }

    /**
     * @Route ("/list/{truck}/{date}", name="get_waypoints_by_truck")
     *
     * @param string $truck
     *
     * @return JsonResponse
     */
    public function getWaypointsByTruckAndDate(
        string $truck,
        \DateTime $date,
        RouteRepository $routeRepository,
        WaypointRepository $waypointRepository
    ): JsonResponse
    {
        try {
            $route = $routeRepository->getRoute($truck, $date);
            $waypoints = $route ? $waypointRepository->findBy(['route' => $route->getId()]) : [];
        } catch (\Exception $exception) {
            return $this->json(['error' => true, 'message' => 'error DB']);
        }

        return $this->json(['waypoints' => $waypoints]);
    }

    /**
     * @Route("/manage", name="waypoint_manage")
     *
     * @return Response
     */
    public function manage()
    {
        $route = new TruckRoute();
        $waypoint = new Waypoint();
        $waypoint->setRoute($route);
        $form = $this->createForm(WaypointType::class, $waypoint);
        $viewForm = $form->createView();

        return $this->render('routing/manage.html.twig', [
            'form' => $viewForm,
            'location' => CompanyController::DEFAULT_LOCATION,
        ]);
    }


    /**
     * @Route("/{id}/edit", name="waypoint_edit", methods={"GET","POST"})
     *
     * @param Request $request
     * @param Waypoint $waypoint
     * @param LogicRepository $logicRepository
     *
     * @return Response
     *
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function edit(Request $request, Waypoint $waypoint, LogicRepository $logicRepository): Response
    {
        $form = $this->createForm(WaypointType::class, $waypoint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->managerRegistry->getManager()->flush();

            return $this->redirectToRoute('route_waypoints', ['route' => $waypoint->getRoute()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('routing/waypoint/edit.html.twig', [
            'waypoint' => $waypoint,
            'documents' => $logicRepository->getDocuments($waypoint->getRoute()->getDateTime()),
            'form' => $form->createView(),
            'location' => CompanyController::DEFAULT_LOCATION,
        ]);
    }

    /**
     * @Route("/{id}", name="waypoint_delete", methods={"POST"})
     *
     * @param Request  $request
     * @param Waypoint $waypoint
     *
     * @return Response
     */
    public function delete(Request $request, Waypoint $waypoint): Response
    {
        if ($this->isCsrfTokenValid('delete'.$waypoint->getId(), $request->request->get('_token'))) {
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->remove($waypoint);
            $entityManager->flush();
        }

        return $this->redirectToRoute('route_waypoints', ['route' => $waypoint->getRoute()->getId()], Response::HTTP_SEE_OTHER);
    }



}
