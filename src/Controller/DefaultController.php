<?php

namespace App\Controller;

use App\Service\ParameterService;
use App\Repository\SectionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @Route("/encrypt/home", name="encrypt_home")
     */
    public function home(
        SectionRepository $sectionRepository,
        ParameterService $parameterService,
        Request $request
    ):Response
    {
        $parameterEncryption = $parameterService->getParameter('ENCRYPTION');

        if( $parameterEncryption && 'encrypt_home' !== $request->attributes->get('_route')) {
            return $this->redirectToRoute('encrypt_home');
        }

        return $this->render('default/home.html.twig', [
            'sections' => $sectionRepository->findAll(),
        ]);
    }
}
