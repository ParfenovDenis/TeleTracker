<?php
/**
 * @license AVT
 */

namespace App\Controller;

use App\Entity\Customers\Company;
use App\Entity\Customers\Driver;
use App\Entity\Relation\DriverTruck;
use App\Form\DriverTruckType;
use App\Form\DriverType;

use App\Form\Handler\ConflictDriverTruckException;
use App\Form\Handler\DriverFormHandler;
use App\Form\Handler\DriverTruckFormHandler;
use App\Repository\Customers\CompanyRepository;
use App\Repository\Customers\DriverRepository;

use App\Repository\Relation\DriverTruckRepository;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Contracts\Translation\TranslatorInterface;

class DriverController extends AbstractController
{
    use NavTrait;

    /**
     * @Route("/company/{companyId}/drivers", name="drivers")
     */
    public function list(DriverRepository $driverRepository, CompanyRepository $companyRepository, $companyId)
    {
        $company = $companyRepository->find($companyId);
        $drivers = $driverRepository->findBy(['company' => $companyId, 'isDeleted' => false]);
        $driverTruck = new DriverTruck();
        $form = $this->createForm(DriverTruckType::class, $driverTruck);
        $cdate = new \DateTime();

        $viewForm = $form->createView();
        return $this->render('driver/list.html.twig', [
            'company' => $company,
            'drivers' => $drivers,
            'form' => $viewForm,
            'cdate' => $cdate
        ]);
    }

    /**
     * @Route("/driver/truck/set", name="set_truck")
     *
     * @param Request $request
     * @param DriverTruckRepository $repository
     *
     * @return JsonResponse
     */
    public function setTruck(Request $request, DriverTruckFormHandler $formHandler): JsonResponse
    {
        $response = new JsonResponse();

        $jsonData = ['success' => false, 'errors' => []];
        try {
            $form = $this->createForm(DriverTruckType::class, new DriverTruck());
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $formHandler->processForm($form);
                }
            }
        } catch (ConflictDriverTruckException $exception) {
            $jsonData['confirm'] = [
                'title' => $exception->getTitle(),
                'body' => $exception->getMessage()
            ];
        }
        $response->setData($jsonData);

        return $response;
    }



    /**
     *
     *
     * @Route("/company/{company}/driver/{driver}", name="driver")
     * @Entity("driver", expr="repository.find(driver)")
     * @Entity("company", expr="repository.find(company)")
     *
     * @param Request $request
     * @param DriverFormHandler $driverFormHandler
     * @param Company|null $company
     * @param Driver|null $driver
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, DriverFormHandler $driverFormHandler, Company $company = null, Driver $driver = null)
    {
        $company = $driver ? $driver->getCompany() : $company;
        $urlParams = [
            'company' => $company->getId(),
            'driver' => $driver ? $driver->getId() : 'new'
        ];
        $form = $this->createForm(DriverType::class, $driver, ['action' => $this->generateUrl('driver', $urlParams)]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$driver)
                $driver = $form->getData();
            $driverFormHandler->processEditForm($driver);
            return $this->redirectToRoute('drivers', ['companyId' => $company->getId()]);
        }

        $params = [
            'form' => $form->createView(),
            'company' => $company
        ];
        if ($driver) {
            $params['title'] = $driver->getLastName();
            $params['id'] = $driver->getId();
        }
        return $this->render('driver/form.html.twig', $params);
    }


    /**
     * @Route("/driver/{id}/delete", name="driver_delete")
     * @param $id
     * @param DriverRepository $driverRepository
     * @param TranslatorInterface $translator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete($id, DriverRepository $driverRepository, TranslatorInterface $translator, Request $request)
    {
        $driver = $driverRepository->find($id);
        if (!$driver) {
            $this->addFlash('warning', $translator->trans('driver_not_found'));
            return $this->redirect($request->headers->get('referer'));
        }
        $driver->setIsDeleted(true);
        $this->addFlash('success', sprintf($translator->trans('driver_delete_success'), $driver->getFIO()));
        $em = $this->managerRegistry->getManager();
        $em->persist($driver);
        $em->flush();
        return $this->redirectToRoute('drivers', ['companyId' => $driver->getCompany()->getId()]);
    }
}