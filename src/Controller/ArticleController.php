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
     * @Route("/article/edit/{article}/{chapter}",
     * name="article_edit",
     * requirements={ "article"="\d+", "chapter"="\d+"},
     * defaults={"article":null, "chapter":null},
     * options={"expose"=true}
     * )
     */
    public function articleEdit(
        Request $request,
        ?Article $article,
        ?Chapter $chapter
    ):Response
    {
        if (null !== $chapter) {
            $article = new Article();
            $article->setChapter($chapter);
        }
        if (null === $chapter && null !== $article) {
            $chapter = $article->getChapter();
        }
        if (null !== $chapter) {
            $article->setSection($chapter->getSection());
        }

        $data = null;
        if ($request->isXmlHttpRequest()) {
            $data = $request->request->get('article');
            $addChapter = ($data['addSection']) ? true : $data['addChapter'];
        }

        $form = $this->createForm(ArticleType::class, $article,[
            'attr' => [
                'data-article' => (null !== $article) ? $article->getId() : null,
            ],
            'add_chapter' => (null !== $data) ? $addChapter : false,
            'add_section' => (null !== $data) ? $data['addSection'] : false,
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            if (null !== $article->getSectionTitle()) {
                $section = new Section();
                $section->setTitle($article->getSectionTitle());
                $this->entityManager->persist($section);
                $this->entityManager->flush($section);
                $article->setSection($section);
            }
            if (null !== $article->getChapterTitle()) {
                $chapter = new Chapter();
                $chapter->setTitle($article->getChapterTitle())
                    ->setSection($article->getSection());
                $this->entityManager->persist($chapter);
                $this->entityManager->flush($chapter);
                $article->setChapter($chapter);
            }
            $user = ($article->getIsPrivate()) ? $this->getUser() : null;
            $article->setUser($user);
            $this->entityManager->persist($article);
            $this->entityManager->flush();

            return $this->redirectToRoute('chapter_show', ['chapter' => $article->getChapter()->getId(), '_fragment' => $article->getId()]);
        }

        return $this->render('article/edit.html.twig', [
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

        return $this->render('article/delete.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }
}
