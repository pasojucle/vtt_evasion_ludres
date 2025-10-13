<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{
    #[Route('/{reactRouting}', name: 'app_home', requirements: ['reactRouting' => '^(?!api|images).+'], defaults: ['reactRouting' => null], methods: ['GET'])]
    public function getApp(ParameterBagInterface $param): Response
    {
        return $this->render('app/App.html.twig', [
            'favicon' => sprintf('build/images/%s', $param->get('favicon')),
            'project_name' => $param->get('project_name'),
        ]);
    }
}
