<?php

declare(strict_types=1);

namespace App\Controller;

use App\UseCase\ModalWindow\ShowModalWindow;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModalWindowController extends AbstractController
{
    #[Route('/modal/window', name: 'modal_window_show', options:['expose' => true], methods: ['GET'])]
    public function show(ShowModalWindow $showModalWindow): Response
    {
        $content = $showModalWindow->execute();
        if (null !== $content) {
            return $this->render('modal_window/show.modal.html.twig', [
                'content' => $content,
            ]);
        }

        return new Response('', 204);
    }
}
