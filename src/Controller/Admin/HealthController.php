<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Health;
use App\Form\Admin\HealthType;
use App\State\Health\Processor\HealthUpdateProcessor;
use App\State\Health\Provider\HealthReadProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/sante', name: 'admin_health')]
class HealthController extends AbstractCrudController
{
    #[Route('/{health}', name: '_show', methods: ['GET'], defaults:['heath' => null])]
    #[IsGranted('USER_EDIT', 'health')]
    public function show(
        HealthReadProvider $provider,
        Health $health,
    ): Response {
        return $this->render('health/admin/show.html.twig', [
            'view' => $provider->getStreamView($health),
        ]);
    }
    
    #[Route('/edit/{health}', name: '_edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'health')]
    public function adminEdit(
        Request $request,
        HealthReadProvider $provider,
        HealthUpdateProcessor $processor,
        Health $health,
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            $health,
            $provider,
            $processor,
            HealthType::class
        );
    }
}
