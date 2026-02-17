<?php
/**
 * @license AVT
 */

namespace App\Controller;

use App\Entity\Customers\Truck;
use App\Form\TruckType;
use App\Repository\Customers\CompanyRepository;
use App\Repository\Customers\TruckRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TruckController
 * @package App\Controller
 */
class TruckController extends AbstractController
{
    use NavTrait;

    /**
     * @Route("/company/{companyId}/trucks", name="trucks")
     */
    public function list(CompanyRepository $companyRepository, $companyId)
    {
        $company = $companyRepository->find($companyId);

        return $this->render('truck/list.html.twig', [
            'company' => $company,
        ]);
    }

    /**
     * @Route("company/{companyId}/truck/new", name="truck_new")
     */
    public function  new($companyId, CompanyRepository $companyRepository)
    {
        $company = $companyRepository->find($companyId);
        $truck = new Truck();
        if ($company)
            $truck->setCompany($company);
        $form = $this->createForm(TruckType::class, $truck);
        return $this->render('truck/form.html.twig', [
            'form' => $form->createView(),
            'company' => $company
        ]);
    }

    /**
     * @Route("/truck/{id}", name="truck")
     * @param Request $request
     * @param TruckRepository $truckRepository
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function truck(Request $request, TruckRepository $truckRepository, $id = null)
    {
        $truck = null;
        if ($id)
            $truck = $truckRepository->find($id);
        if (!$truck)
            $truck = new Truck();
        $form = $this->createForm(TruckType::class, $truck);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var Truck $truck
             */
            $truck = $form->getData();
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($truck);
            $entityManager->flush();
            return $this->redirectToRoute('trucks', ['companyId' => $truck->getCompany()->getId()]);
        }
        $params = [
            'form' => $form->createView(),
            'company' => $truck->getId() ? $truck->getCompany() : $request->get('company')
        ];
        if ($truck->getId())
            $params['title'] = $truck->getBrand() . " " . $truck->getModel();

        $params['id'] = $truck->getId();
        return $this->render('truck/form.html.twig', $params);
    }


}
