<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\AutocompleteHandler;
use App\Core\Handler\ListExportHandler;
use App\Core\Handler\ListJsonHandler;
use App\Core\Handler\ListPaginatedHandler;
use App\Dto\Filter\RegistrationFilter;
use App\State\Registration\Provider\RegistrationAutocompletProvider;
use App\State\Registration\Provider\RegistrationListEmailProvider;
use App\State\Registration\Provider\RegistrationListExportProvider;
use App\State\Registration\Provider\RegistrationListProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name:'admin_registration')]
class RegistrationController extends AbstractController
{
    #[Route('/inscriptions', name: '_list', methods: ['GET', 'POST'])]
    #[IsGranted('USER_LIST')]
    public function adminRegistrations(
        Request $request,
        RegistrationListProvider $provider,
        ListPaginatedHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
        );
    }

    #[Route('/export/inscription', name: 's_export', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminRegistrationsExport(
        Request $request,
        RegistrationListExportProvider $provider,
        ListExportHandler $handler,
    ): StreamedResponse {
        return $handler->handle($request, RegistrationFilter::class, $provider);
    }

    #[Route('/emails/inscriptions', name: 's_email_to_clipboard', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminEmailRegistrations(
        Request $request,
        RegistrationListEmailProvider $provider,
        ListJsonHandler $handler,
    ): JsonResponse {
        return $handler->handle(
            $request,
            $provider,
            RegistrationFilter::class,
        );
    }

    #[Route('/inscription/autocomplete', name: '_autocomplete', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function memberAutocomplete(
        Request $request,
        RegistrationAutocompletProvider $provider,
        AutocompleteHandler $handler,
    ): JsonResponse {
        return $handler->handle(
            $request,
            $provider,
            RegistrationFilter::class,
        );
    }
}
