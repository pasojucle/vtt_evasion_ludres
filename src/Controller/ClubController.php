<?php

namespace App\Controller;


use App\Repository\ContentRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ClubController extends AbstractController
{
    /**
     * @Route("/club", name="club_detail")
     */
    public function clubDetail(
        ContentRepository $contentRepository
    ): Response
    {

        return $this->render('club/detail.html.twig', [
            'content' => $contentRepository->findOneByRoute('club_detail'),
        ]);
    }
}
