<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\LogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/log', name: 'log_')]
class LogController extends AbstractController
{
    #[Route('/write', name: 'write', methods: ['POST'], options:['expose' => true])]
    public function write(Request $request, LogService $logService): JsonResponse
    {
        $log = $request->request->all('log');
        /** @var ?User $user */
        $user = $this->getUser();
        if ($user) {
            $logService->write($log['entityName'], (int) $log['entityId'], $user);
        }

        return new JsonResponse(['codeError' => 0]);
    }
}
