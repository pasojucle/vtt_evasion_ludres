<?php

namespace App\Controller;

use App\Service\MenuService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'sitemap')]
    public function sitemap(MenuService $menuService): Response
    {
        $urls = [];
        foreach ($menuService->getIndexableRoutes() as $menu) {
            $urls[] = [
                'loc' =>  $this->generateUrl($menu['route'], [], UrlGeneratorInterface::ABSOLUTE_URL),
                'priority' => $menu['priority']
            ];
        }

        $response = new Response(
            $this->renderView('sitemap/sitemap.html.twig', [
                    'urls' => $urls,
            ])
        );
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }

    #[Route('/robots.txt', name: 'robots')]
    public function robots(MenuService $menuService): Response
    {
        $content = "User-agent : *".PHP_EOL.PHP_EOL
            . 'Sitemap : ' . $this->generateUrl('sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL); 
        $response = new Response(
            $content
        );
        $response->headers->set('Content-Type', 'text/txt');
        return $response;
    }
}
