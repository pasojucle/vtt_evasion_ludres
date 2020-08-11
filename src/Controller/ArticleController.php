<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Chapter;
use App\Entity\Section;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/article/set/{addSection}/{addChapter}/{article}",
     * name="article_set",
     * requirements={"addSection"="\d+", "addChapter"="\d+", "article"="\d+"},
     * defaults={"addSection":0, "addChapter":0, "article":null},
     * options={"expose"=true}
     * )
     */
    public function articleSet(
        Request $request,
        bool $addSection,
        bool $addChapter,
        ?Article $article
    ):Response
    {
        if ($addSection) {
            $addChapter = true;
        }

        if (null !== $article) {
            $article->setSection($article->getChapter()->getSection());
        }

        $form = $this->createForm(ArticleType::class, $article,[
            'attr' => [
                'data-article' => (null !== $article) ? $article->getId() : null,
            ],
            'add_chapter' => $addChapter,
            'add_section' => $addSection,
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            if (null !== $article->getSectionTitle()) {
                $section = new Section();
                $section->setTitle($article->getSectionTitle());
                $this->entityManager->persist($section);
                $this->entityManager->flush();
                $article->setSection($section);
            }
            if (null !== $article->getChapterTitle()) {
                $chapter = new Chapter();
                $chapter->setTitle($article->getChapterTitle())
                    ->setSection($article->getSection());
                $this->entityManager->persist($chapter);
                $this->entityManager->flush();
                $article->setChapter($chapter);
            }
            $this->entityManager->persist($article);
            $this->entityManager->flush();

            return $this->redirectToRoute('chapter_show', ['chapter' => $article->getChapter()->getId(), '_fragment' => $article->getId()]);
        }

        return $this->render('article/articleSet.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/delete/{article}",
     * name="article_delete",
     * requirements={"article"="\d+"},
     * )
     */
    public function articleDelete(
        Request $request,
        ?Article $article
    ):Response
    {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl('article_delete', 
            [
                'article'=> $article->getId(),
            ]
        ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($article);
            $this->entityManager->flush();

            return $this->redirectToRoute('chapter_show', [
                'chapter' => $article->getChapter()->getId(),
            ]);
        }

        return $this->render('article/articleDelete.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }
}
