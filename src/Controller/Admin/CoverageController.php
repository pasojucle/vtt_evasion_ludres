<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\AutocompleteHandler;
use App\Core\Handler\ListExportHandler;
use App\Core\Handler\ListJsonHandler;
use App\Core\Handler\ListPaginatedHandler;
use App\Dto\Filter\CoverageFilter;
use App\Entity\Licence;
use App\State\Coverage\Processor\CoverageValidateProcessor;
use App\State\Coverage\Provider\CoverageAutocompleteProvider;
use App\State\Coverage\Provider\CoverageListEmailProvider;
use App\State\Coverage\Provider\CoverageListExportProvider;
use App\State\Coverage\Provider\CoverageListProvider;
use App\State\Coverage\Provider\CoverageValidateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/assurance', name: 'admin_coverage')]
class CoverageController extends AbstractController
{
    #[Route('s', name: '_list', methods: ['GET', 'POST'])]
    #[IsGranted('USER_LIST')]
    public function list(
        Request $request,
        CoverageListProvider $provider,
        ListPaginatedHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
        );
    }

    #[Route('validate/{licence}', name: '_validate', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminRegistartionValidate(
        Request $request,
        CoverageValidateProvider $provider,
        CoverageValidateProcessor $processor,
        Licence $licence,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $licence,
            $provider,
            $processor
        );
    }

    #[Route('/export', name: 's_export', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminCoveragesExport(
        Request $request,
        CoverageListExportProvider $provider,
        ListExportHandler $handler,
    ): Response {
        return $handler->handle(
            request: $request,
            provider: $provider,
            filterClass: CoverageFilter::class
        );
    }

    #[Route('/emails', name: 's_email_to_clipboard', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminEmailCoverages(
        Request $request,
        CoverageListEmailProvider $provider,
        ListJsonHandler $handler,
    ): JsonResponse {
        return $handler->handle(
            $request,
            $provider,
            CoverageFilter::class
        );
    }

    #[Route('/autocomplete', name: '_autocomplete', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function memberAutocomplete(
        Request $request,
        CoverageAutocompleteProvider $provider,
        AutocompleteHandler $handler,
    ): JsonResponse {
        return $handler->handle(
            $request,
            $provider,
            CoverageFilter::class,
        );
    }
}
