<?php

declare(strict_types=1);

namespace App\Controller;

use App\UseCase\Error\GetError;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ErrorController extends AbstractController
{
    #[Route('/erreur', name: 'error', methods: ['GET'])]
    public function show(Request $request, EntityManagerInterface $entityManager, GetError $getError)
    {
        $entityManager->clear();
        $logError = $getError->execute($request);
        
        if ($logError->getPersist()) {
            $entityManager->persist($logError);
            $entityManager->flush();
        }

        return $this->render('error/error.html.twig', [
            'error' => $logError,
        ]);
    }
}
