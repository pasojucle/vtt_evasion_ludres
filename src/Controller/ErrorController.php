<?php

namespace App\Controller;

use App\UseCase\Error\GetError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ErrorController extends AbstractController
{
    /**
     * @Route("/erreur", name="error")
     */
    public function show(Request $request, EntityManagerInterface $entityManager, GetError $getError)
    {
        $logError = $getError->execute($request);
        
        if ($logError->getPersist()) {
            if (!$entityManager->isOpen()) {
                $entityManager = $entityManager->create(
                  $entityManager->getConnection(), $entityManager->getConfiguration());
              }
            $entityManager->persist($logError);
            $entityManager->flush();
        }

        return $this->render('error/error.html.twig', [
            'error' => $logError,
        ]);
    }
}