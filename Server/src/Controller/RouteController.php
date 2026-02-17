<?php
/**
 * @license AVT
 */

namespace App\Controller;

use App\Entity\Routing\Route as TruckRoute;
use App\Form\RouteType;
use App\Repository\Routing\RouteRepository;
use App\Repository\Routing\WaypointRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * @Route("/routing/route")
 *
 * @IsGranted("ROLE_USER")
 */
class RouteController extends AbstractController
{

    use NavTrait;
    /**
     * @Route("/list/{date}", name="routing_route_index", methods={"GET"})
     *
     * @param RouteRepository $routeRepository
     * @param null $date
     *
     * @return Response
     */
    public function list(RouteRepository $routeRepository, $date = null): Response
    {
        try {
            $dateTime = new \DateTime($date ?? 'now');
        } catch (\Exception $exception) {
            $this->addFlash('error', $this->translator->trans('wrong_format_date'));
            $dateTime = new \DateTime();
        }

        return $this->render('routing/route/list.html.twig', [
            'routes' => $routeRepository->getRoutes($dateTime),
            'default_datetime' => $dateTime,
        ]);
    }

    /**
     * @Route("/new", name="routing_route_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $route = new TruckRoute();
        try {
            $dateTime = new \DateTime($request->get('date', 'now'));
        } catch (\Exception $exception) {
            $this->addFlash('error', $this->translator->trans('wrong_format_date'));
            $dateTime = new \DateTime();
        }
        $route->setDateTime($dateTime);
        $form = $this->createForm(RouteType::class, $route);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($route);
            $entityManager->flush();

            return $this->redirectToRoute('routing_route_index',[]);
        }

        return $this->render('routing/route/new.html.twig', [
            'route' => $route,
            'form' => $form->createView(),
            'default_datetime' => $dateTime,
        ]);
    }


    /**
     * @Route("/{route}/waypoints", name="route_waypoints", methods={"GET"})
     */
    public function getWaypoints(WaypointRepository $waypointRepository, RouteRepository $routeRepository, int $route): Response
    {
        return $this->render('routing/waypoint/list.html.twig', [
            'waypoints' => $waypointRepository->findBy(['route' => $route]),
            'route' => $routeRepository->find($route),
        ]);
    }

    /**
     * @Route("/{id}", name="routing_route_show", methods={"GET"})
     */
    public function show(TruckRoute $route): Response
    {
        return $this->render('routing/route/show.html.twig', [
            'route' => $route,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="routing_route_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, TruckRoute $route): Response
    {
        $form = $this->createForm(RouteType::class, $route);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->managerRegistry->getManager()->flush();

            return $this->redirectToRoute('routing_route_index');
        }

        return $this->render('routing/route/edit.html.twig', [
            'route' => $route,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="routing_route_delete", methods={"DELETE"})
     */
    public function delete(Request $request, TruckRoute $route): Response
    {
        if ($this->isCsrfTokenValid('delete' . $route->getId(), $request->request->get('_token'))) {
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->remove($route);
            $entityManager->flush();
        }

        return $this->redirectToRoute('routing_route_index');
    }
}
