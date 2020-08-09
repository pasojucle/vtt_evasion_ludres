<?php

namespace App\Controller;

use App\Entity\Chapter;
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
     */
    public function ChapterShow(
        ?Chapter $chapter
    ):Response
    {
  
        return $this->render('chapter/chapterShow.html.twig',[
            'chapter' => $chapter,
        ]);
    }
}
