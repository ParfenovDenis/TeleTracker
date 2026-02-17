<?php
/**
 * Copyright (c) 2020.
 */

namespace App\Controller;

use App\Entity\Customers\GroupCompanies;

use App\Repository\Customers\GroupCompaniesRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

trait NavTrait
{
    protected $translator;

    protected $managerRegistry;

    /**
     * NavTrait constructor.
     * @param TranslatorInterface $translator
     * @param ManagerRegistry     $managerRegistry
     */
    public function __construct(TranslatorInterface $translator, ManagerRegistry $managerRegistry)
    {
        $this->translator = $translator;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $view
     * @param array $parameters
     * @param Response|null $response
     *
     * @return Response
     */

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        /**
         * @var GroupCompaniesRepository $groupRepository
         */
        $groupRepository = $this->managerRegistry->getRepository(GroupCompanies::class);
        $groups = $groupRepository->findAll();
        $parameters['groups'] = $groups;

        return parent::render($view, $parameters, $response);
    }
}
