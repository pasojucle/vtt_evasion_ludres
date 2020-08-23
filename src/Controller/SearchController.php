<?php

namespace App\Controller;

use App\Service\SearchService;
use App\Service\ParameterService;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    /**
     * @Route("/search",
     *  name="search"
     * )
     */
    public function search(
        Request $request,
        SearchService $serchService,
        ArticleRepository $articleRepository,
        ParameterService $parameterService
    ):Response
    {
        $form_search = $serchService->getForm();
 
        $form_search->handleRequest($request);
        $articles = [];
        $term = null;
        if ($request->isMethod('post') && $form_search->isSubmitted() && $form_search->isValid()) {
            $data = $form_search->getData();
            $term = $data['term'];
            $searchs = preg_split('#\s#', htmlentities($term), PREG_SPLIT_NO_EMPTY);

            $user = $this->getUser();

            if ($parameterService->getParameter('ENCRYPTION')) {
                $allArticles = $articleRepository->findAll();
                $articles = [];
                $pattern = '#'.implode('|', $searchs).'#';
                if (null !== $allArticles) {
                    foreach($allArticles as $article) {
                        if (\preg_match($pattern, $article->getChapter()->getSection()->getTitle()) || \preg_match($pattern, $article->getChapter()->getTitle())
                        || \preg_match($pattern, $article->getTitle()) || \preg_match($pattern, $article->getContent())) {
                            if (null === $article->getUser() || $user === $article->getUser()) {
                                $articles[] = $article;
                            }
                        }
                    }
                }
            } else {
                $articles = $articleRepository->findByTerm($searchs, $user);
            }
        }

        return $this->render('search/show.html.twig', [
            'term' => $term,
            'articles' => $articles,
            'form_search' => $form_search->createView(),
        ]);
    }
}
