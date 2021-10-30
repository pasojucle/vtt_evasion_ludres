<?php

namespace App\Controller;

use App\Service\MailerService;
use App\UseCase\Error\GetError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ErrorController extends AbstractController
{
    /**
     * @Route("/erreur", name="error")
     */
    public function show(Request $request, MailerService $mailerService, GetError $getError)
    {
        $error = $getError->execute($request);
dump($error);
        if ($error['sendMessage']) {
            $mailerService->sendError($error);
        }

        return $this->render('error/error.html.twig', [
            'error' => $error,
        ]);
    }
}