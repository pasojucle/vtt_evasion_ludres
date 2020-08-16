<?php

namespace App\Controller;

use App\Entity\Chapter;
use App\Service\ParameterService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChapterController extends AbstractController
{
    
    /**
     * @Route("/chapter/show/{chapter}",
     * name="chapter_show",
     * requirements={"chapter"="\d+"}
     * )
     * 
     * @Route("encrypt/chapter/show/{chapter}",
     * name="encrypt_chapter_show",
     * requirements={"chapter"="\d+"}
     * )
     */
    public function ChapterShow(
        ParameterService $parameterService,
        Request $request,
        ?Chapter $chapter
    ):Response
    {
        $parameterEncryption = $parameterService->getParameter('ENCRYPTION');

        if( $parameterEncryption && 'encrypt_chapter_show' !== $request->attributes->get('_route')) {
            return $this->redirectToRoute('encrypt_chapter_show', ['chapter' => $chapter->getId()]);
        }

        return $this->render('chapter/chapterShow.html.twig',[
            'chapter' => $chapter,
        ]);
    }
}
