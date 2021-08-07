<?php

namespace App\Controller;

use App\Repository\ContentRepository;
use App\Repository\LevelRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SchoolController extends AbstractController
{
    /**
     * @Route("/ecole_vtt", name="school_detail")
     */
    public function schoolDetail(
        ContentRepository $contentRepository,
        LevelRepository $levelRepository
    ): Response
    {

        return $this->render('school/detail.html.twig', [
            'content' => $contentRepository->findOneByRoute('school_detail'),
            'levels' => $levelRepository->findAllTypeMember(),
        ]);
    }
}
