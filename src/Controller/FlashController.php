<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FlashController extends AbstractController
{
    #[Route('/admin/flashes', name: 'app_flashes')]
    public function renderFlashes(
        Request $request
    ): Response
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();

        return $this->render('components/_flashes_frame.html.twig', [
            'flashes' => $session->getFlashBag()->all(),
        ]);
    }
}