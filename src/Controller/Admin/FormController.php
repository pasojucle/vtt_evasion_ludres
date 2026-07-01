<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/form', name: 'admin_form_')]
class FormController extends AbstractController
{

    #[Route('/icon/choices', name: 'icon_choices', methods: ['POST'])]
    public function choices(
        Request $request,
    ): Response {

        return $this->render('form/_ux_icon_choices.stream.html.twig', [
            'choices' => json_decode($request->request->get('choices', '[]'), true),
            'value' => $request->request->get('value'),
            'action' => $request->request->get('action'),
        ]);
    }
}