<?php
/**
 * @license AVT
 */

namespace App\Controller;

use App\Entity\Security\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index()
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        try {
            return $this->render('user/index.html.twig', [
                'form' => $form->createView(),
            ]);
        } catch (\Exception $e)
        {
            $e;
        }
    }
}
