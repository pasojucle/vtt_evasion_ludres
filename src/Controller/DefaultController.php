<?php

namespace App\Controller;

use App\Repository\SectionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(
        SectionRepository $sectionRepository
    ):Response
    {

        return $this->render('default/home.html.twig', [
            'sections' => $sectionRepository->findAll(),
        ]);
    }
}
