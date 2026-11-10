<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\DetailHandler;
use App\Entity\Health;
use App\Form\Admin\HealthType;
use App\State\Health\Processor\HealthUpdateProcessor;
use App\State\Health\Provider\HealthReadProvider;
use App\State\Health\Provider\HealthUpdateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/sante', name: 'admin_health')]
class HealthController extends AbstractController
{
    #[Route('/{health}', name: '_show', methods: ['GET'], defaults:['heath' => null])]
    #[IsGranted('USER_EDIT', 'health')]
    public function show(
        Request $request,
        HealthReadProvider $provider,
        Health $health,
        DetailHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
            $health,
        );
    }
    
    #[Route('/edit/{health}', name: '_edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'health')]
    public function adminEdit(
        Request $request,
        HealthUpdateProvider $provider,
        HealthUpdateProcessor $processor,
        Health $health,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $health,
            $provider,
            $processor,
            HealthType::class
        );
    }
}
