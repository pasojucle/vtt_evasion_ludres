<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\Filter\RegistrationFilter;
use App\State\Registration\Provider\RegistrationListProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name:'admin_registration')]
class RegistrationController extends AbstractCrudController
{
    #[Route('/inscriptions', name: '_list', methods: ['GET', 'POST'])]
    #[IsGranted('USER_LIST')]
    public function adminRegistrations(
        RegistrationListProvider $provider,
        Request $request,
    ): Response {
        return $this->handleListAction(
            RegistrationFilter::class,
            $provider,
            $request
        );
    }

    #[Route('/export/inscription', name: 's_export', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminRegistrationsExport(
        RegistrationListProvider $provider,
        Request $request
    ): StreamedResponse {
        return $this->handleExportAction($request, RegistrationFilter::class, $provider, 'export_assurances.csv');
    }

    #[Route('/emails/inscriptions', name: 's_email_to_clipboard', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminEmailRegistrations(
        RegistrationListProvider $provider,
        Request $request
    ): JsonResponse {
        /**  @var RegistrationFilter $filter */
        $filter = $provider->getHydratedDto($request->query->all(), RegistrationFilter::class);

        return new JsonResponse($provider->copyEmailListToClipboard($filter));
    }

    #[Route('/inscription/autocomplete', name: '_autocomplete', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function memberAutocomplete(
        RegistrationListProvider $provider,
        Request $request
    ): JsonResponse {
        /**  @var RegistrationFilter $filter */
        $filter = $provider->getHydratedDto($request->query->all(), RegistrationFilter::class);
        $query = $request->query->get('query');

        return new JsonResponse(['results' => $provider->getAutocompleteChoices($query, $filter)]);
    }
}
