<?php
/**
 * @license AVT
 */

namespace App\Controller;

use App\Entity\Customers\Company;
use App\Form\CompanyType;
use App\Form\FilialType;
use App\Repository\Customers\CompanyRepository;

use App\Repository\Customers\GroupCompaniesRepository;

use App\Repository\Customers\Validator\Company\CompanyValidator;
use App\Repository\Customers\Validator\Company\Exception\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class CompanyController
 * @package App\Controller
 * @IsGranted("ROLE_USER")
 */
class CompanyController extends AbstractController
{
    const DEFAULT_LOCATION = ['lat' => 55.979159, 'lng' => 37.299112, 'radius' => 40]; // TODO: запросить местоположение на js
    use NavTrait;




    /**
     * @Route("/companies", name="companies")
     * @param CompanyRepository $companyRepository
     * @return Response
     */
    public function list(CompanyRepository $companyRepository)
    {
        $companies = $companyRepository->findBy(['parent' => null]);
        return $this->render("company/list.html.twig", ['companies' => $companies]);
    }

    /**
     * @Route("/company/{companyId}/location", name="company_location")
     * @param Request $request
     * @param CompanyRepository $companyRepository
     * @param $companyId
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateLocation(Request $request, CompanyRepository $companyRepository, $companyId)
    {
        $result = false;
        try {
            if ($company = $companyRepository->find($companyId)) {
                $company
                    ->setLat($request->get('lat'))
                    ->setLng($request->get('lng'))
                    ->setRadius($request->get('radius'));
                $entityManager = $this->managerRegistry->getManager();
                $entityManager->persist($company);
                $entityManager->flush();
                $result = true;
            }
        } catch (\Exception $exception) {
            $result = false;
        }

        return $this->json(['result' => $result]);
    }

    /**
     * @Route("/company/{id}/filials", name="filials")
     * @param CompanyRepository $companyRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function filials(CompanyRepository $companyRepository, $id)
    {
        $filials = $companyRepository->findBy(["parent" => $id]);
        $company = $companyRepository->find($id);
        return $this->render("filial/list.html.twig", ['filials' => $filials, 'company' => $company]);
    }


    /**
     * @Route("/group/{id}/companies", name="group_companies")
     * @param GroupCompaniesRepository $groupCompaniesRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listByGroup(GroupCompaniesRepository $groupCompaniesRepository, $id)
    {
        $group = $groupCompaniesRepository->find($id);

        return $this->render("company/list.html.twig", [
            'companies' => $group->getCompanies(),
            'group' => $group
        ]);
    }

    /**
     * @Route("/company/{id}/delete", name="company_delete")
     * @param int $id
     * @param CompanyRepository $companyRepository
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(int                 $id,
                           CompanyRepository   $companyRepository,
                           TranslatorInterface $translator,
                           Request             $request): RedirectResponse
    {
        try {
            $company = $companyRepository->find($id);
            if (!$company)
                throw new \Exception($translator->trans("company_not_found"));
            $validator = new CompanyValidator($company);
            $validator->validate();
            $em = $this->managerRegistry->getManager();
            $em->remove($company);
            $em->flush();
            $this->addFlash('success', sprintf($translator->trans('company_delete_success'), $company->getTitle()));
            if ($company->getParent()) {
                return $this->redirectToRoute('filials', ['id' => $company->getParent()->getId()]);
            }
            return $this->redirectToRoute('group_companies', ['id' => $company->getGroupCompanies()->getId()]);
        } catch (ValidationException $exception) {
            array_map(function ($error) {
                $this->addFlash('warning', $error);
            }, $exception->getErrors());

            return $this->redirect($request->headers->get('referer'));
        } catch (\Exception $exception) {
            $this->addFlash('warning', $exception->getMessage());

            return $this->redirect($request->headers->get('referer'));
        }

    }

    /**
     * @Route("/company/{parent}/filial/new", name="filial")
     * @Route("/company/{id}", name="company")
     */
    public function companyForm(Request $request, CompanyRepository $companyRepository, TranslatorInterface $translator, $id = null, $parent = null)
    {
        $company = null;
        if ($id) {
            $company = $companyRepository->find($id);
            $title = $company->getTitle();
        }
        if (!$company) {
            $company = new Company();
            if ($parent)
                $company->setParent($companyRepository->find($parent));
            $title = $parent ? $translator->trans("filial_new") : $translator->trans("company_new");
        }
        $type = $company->getParent() ? FilialType::class : CompanyType::class;
        $form = $this->createForm($type, $company);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /**
                 * @var Company $company
                 */
                $company = $form->getData();
                $entityManager = $this->managerRegistry->getManager();
                $entityManager->persist($company);
                $entityManager->flush();

                if ($group = $company->getGroupCompanies()) {
                    return $this->redirectToRoute('group_companies', ['id' => $group->getId()]);
                }
                return $company->getParent() ? $this->redirectToRoute('filials', ['id' => $company->getParent()->getId()]) : $this->redirectToRoute('companies');
            }
        }
        $location = ['lat' => $company->getLat(), 'lng' => $company->getLng(), 'radius' => $company->getRadius()];
        if (!$location['lat'] || !$location['lng'])
        {
            $location = self::DEFAULT_LOCATION;
        }
        $formView = $form->createView();
        $response = $this->render('company/form.html.twig', [
            'form' => $formView,
            'location' => $location,
            'title' => $title,
            'company_id' => ($company->getId() ?:
                ($company->getParent() ? $company->getParent()->getId() : null)),
            'group_id' => $company->getGroupCompanies() ? $company->getGroupCompanies()->getId() : null,
            'url_tile_server' => TeleTrackerController::URL_TILE_SERVER
        ]);

        return $response;
    }

}
