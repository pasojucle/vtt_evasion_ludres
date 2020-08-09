<?php

namespace App\Controller;

use App\Service\SearchService;
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
        ArticleRepository $articleRepository
    ):Response
    {
        $form_search = $serchService->getForm();
 
        $form_search->handleRequest($request);
        $articles = [];
        $term = null;
        if ($request->isMethod('post') && $form_search->isSubmitted() && $form_search->isValid()) {
            $data = $form_search->getData();
            $term = $data['term'];
            $articles = $articleRepository->findByTerm($term);
        }

        return $this->render('search/show.html.twig', [
            'term' => $term,
            'articles' => $articles,
            'form_search' => $form_search->createView(),
        ]);
    }
}
