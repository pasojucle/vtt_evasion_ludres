<?php

namespace App\Controller;

use App\Entity\Chapter;
use App\Form\ChapterType;
use App\Service\ParameterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** @Route("/chapter") */
class ChapterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/show/{chapter}",
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

    /**
     * @Route("/edit/{chapter}", name="chapter_edit")
     */
    public function edit(Request $request, Chapter $chapter)
    {
        $form = $this->createForm(ChapterType::class, $chapter,[
            'action' => $this->generateUrl('chapter_edit', ['chapter' => $chapter->getId()]),
        ]);


        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $chapter = $form->getData();
            $this->entityManager->flush();

            return $this->redirectToRoute('chapter_show',[
                'chapter' => $chapter->getId(),
            ]);
        }

        return $this->render('chapter/edit.html.twig', [
            'chapter' => $chapter,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{chapter}", name="chapter_delete")
     */
    public function delete(Request $request, Chapter $chapter)
    {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl('chapter_delete', [
                'chapter'=> $chapter->getId(),
            ]),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($chapter);
            $this->entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('chapter/delete.html.twig', [
            'chapter' => $chapter,
            'form' => $form->createView(),
        ]);
    }
}
