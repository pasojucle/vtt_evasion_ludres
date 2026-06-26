<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\Filter\CoverageFilter;
use App\Entity\Licence;
use App\State\Coverage\Processor\CoverageValidateProcessor;
use App\State\Coverage\Provider\CoverageListProvider;
use App\State\Coverage\Provider\CoverageValidateProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/assurance', name: 'admin_coverage')]
class CoverageController extends AbstractCrudController
{
    #[Route('s', name: '_list', methods: ['GET', 'POST'])]
    #[IsGranted('USER_LIST')]
    public function list(
        CoverageListProvider $provider,
        Request $request,
    ): Response {
        return $this->handleListAction(
            CoverageFilter::class,
            $provider,
            $request
        );
    }

    #[Route('validate/{licence}', name: '_validate', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminRegistartionValidate(
        Request $request,
        CoverageValidateProvider $provider,
        CoverageValidateProcessor $processor,
        Licence $licence
    ): Response {
        return $this->handleFormComponentAction(
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
        CoverageListProvider $provider,
    ): Response {
        return $this->handleExportAction($request, CoverageFilter::class, $provider, 'export_inscriptions.csv');
    }

    #[Route('/emails', name: 's_email_to_clipboard', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminEmailCoverages(
        CoverageListProvider $provider,
        Request $request
    ): JsonResponse {
        /**  @var CoverageFilter $filter */
        $filter = $provider->getHydratedDto($request->query->all(), CoverageFilter::class);

        return new JsonResponse($provider->copyEmailListToClipboard($filter));
    }

    #[Route('/autocomplete', name: '_autocomplete', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function memberAutocomplete(
        CoverageListProvider $provider,
        Request $request
    ): JsonResponse {
        /**  @var CoverageFilter $filter */
        $filter = $provider->getHydratedDto($request->query->all(), CoverageFilter::class);

        return new JsonResponse(['results' => $provider->getAutocompleteChoices($filter)]);
    }
}
