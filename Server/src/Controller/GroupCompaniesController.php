<?php
/**
 * @license AVT
 */

namespace App\Controller;

use App\Entity\Customers\GroupCompanies;
use App\Form\GroupCompaniesType;
use App\Repository\Customers\CompanyRepository;
use App\Repository\Customers\GroupCompaniesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class GroupCompaniesController extends AbstractController
{
    use NavTrait;

    /**
     * @Route("/group/{id}", name="group")
     */
    public function company(Request $request, GroupCompaniesRepository $groupCompaniesRepository, TranslatorInterface $translator, int $id = null)
    {

        $group = null;
        if ($id) {
            $group = $groupCompaniesRepository->find($id);

        }

        $group ?? new GroupCompanies();
        $title = $group->getId() ?$group->getTitle(): $translator->trans("group_companies_new");
        $form = $this->createForm(GroupCompaniesType::class, $group);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $group = $form->getData();
                $entityManager = $this->managerRegistry->getManager();
                $entityManager->persist($group);
                $entityManager->flush();
                return $this->redirectToRoute('groups');
            }
        }
        $response = $this->render('group_companies/form.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
            'id' => $group->getId()
        ]);
        return $response;
    }

    /**
     * @Route("/groups", name="groups")
     * @param GroupCompaniesRepository $groupCompaniesRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(GroupCompaniesRepository $groupCompaniesRepository)
    {
        $groupCompanies = $groupCompaniesRepository->findAll();
        return $this->render("group_companies/list.html.twig", ['group_companies' => $groupCompanies]);
    }


}
