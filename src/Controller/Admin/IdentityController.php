<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Identity;
use App\Form\IdentityType;
use App\State\Identity\Processor\IdentityUpdateProcessor;
use App\State\Identity\Provider\IdentityReadProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/identite', name: 'admin_identity')]
class IdentityController extends AbstractCrudController
{
    #[Route('/show/{identity}', name: '_show', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'identity')]
    public function show(
        IdentityReadProvider $provider,
        Identity $identity,
    ): Response {
        return $this->render('identity/admin/show.html.twig', [
            'view' => $provider->getStreamView($identity),
        ]);
    }
    
    #[Route('/edit/{identity}', name: '_edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'identity')]
    public function adminEdit(
        Request $request,
        IdentityReadProvider $provider,
        IdentityUpdateProcessor $processor,
        Identity $identity,
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            $identity,
            $provider,
            $processor,
            IdentityType::class
        );
    }
}
