<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\DetailHandler;
use App\Entity\Licence;
use App\Form\Admin\LicenceAuthorizationsType;
use App\State\LicenceAuthorization\Processor\LicenceAuthorizationsUpdateProcessor;
use App\State\LicenceAuthorization\Provider\LicenceAuthorizationsReadProvider;
use App\State\LicenceAuthorization\Provider\LicenceAuthorizationsUpdateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/autorisations/licence', name: 'admin_licence_authorizations')]
class LicenceAuthorizationController extends AbstractController
{
    #[Route('/{licence}', name: '_show', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function show(
        Request $request,
        LicenceAuthorizationsReadProvider $provider,
        Licence $licence,
        DetailHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
            $licence,
        );
    }
    
    #[Route('/edit/{licence}', name: '_edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminEdit(
        Request $request,
        LicenceAuthorizationsUpdateProvider $provider,
        LicenceAuthorizationsUpdateProcessor $processor,
        Licence $licence,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $licence,
            $provider,
            $processor,
            LicenceAuthorizationsType::class
        );
    }
}
