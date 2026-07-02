<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\State\IconChoices\Provider\IconChoicesListProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/icon-choices', name: 'admin_icon_choices_')]
class IconChoicesController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['POST'])]
    public function list(
        Request $request,
        IconChoicesListProvider $provider
    ): Response {
        return $this->render('form/_ux_icon_choices.stream.html.twig', [
            'list' => $provider->getCollection(
                json_decode($request->request->get('choices', '[]'), true),
                $request->request->get('value'),
                $request->request->get('action'),
            )
        ]);
    }
}
