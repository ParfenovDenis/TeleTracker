<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     *
     *
     */
    public function index()
    {
        return $this->redirectToRoute('app_login');

    }
}
