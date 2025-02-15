<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        $form = $this->createForm(LoginType::class, [
            'licenceNumber' => $authenticationUtils->getLastUsername(),
            'skipSplash' => (bool) $request->cookies->get('skip_splash'),
        ]);

        return $this->render('security/login.html.twig', [
            'form' => $form->createView(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/deconnexion', name: 'check_logout', methods: ['GET', 'POST'])]
    public function checkLogout(
        Request $request,
        FormFactoryInterface $formFactory
    ) {
        $form = $formFactory->create();
        if ($request->isXmlHttpRequest()) {
            return $this->render('security/logout.modal.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        $request->getSession()->remove('user_fullName');
        return $this->redirectToRoute('app_logout');
    }
}
