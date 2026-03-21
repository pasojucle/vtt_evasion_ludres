<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Member;
use App\Service\LogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/log', name: 'log_')]
class LogController extends AbstractController
{
    #[Route('/write', name: 'write', methods: ['POST'], options:['expose' => true])]
    public function write(Request $request, LogService $logService): JsonResponse
    {
        /** @var ?Member $member */
        $member = $this->getUser();
        $form = $logService->getForm();

        $form->handleRequest($request);
        if ($member && $form->isSubmitted() && $form->isValid()) {
            $log = $form->getData();
            $logService->write($log['entityName'], (int) $log['entityId'], $member);
        }

        return new JsonResponse(['codeError' => 0]);
    }
}
