<?php

namespace App\Controller;

use App\Form\LoginType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $form = $this->createForm(LoginType::class, ['licenceNumber' => $lastUsername]);

        return $this->render('security/login.html.twig', ['form' => $form->createView(), 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/deconnexion", name="check_logout")
     */
    public function checkLogout(
        Request $request,
        FormFactoryInterface $formFactory
        )
    {
        $form = $formFactory->create();
        if ($request->isXmlHttpRequest()) {
            return $this->render('security/logout.modal.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        return $this->redirectToRoute('app_logout');
    }
}
