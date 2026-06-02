<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\Filter\RegistrationFilter;
use App\Form\Filter\ListFilterType;
use App\State\Registration\Provider\RegistrationListProvider;
use App\UseCase\Registration\GetRegistrationsFiltered;
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
        RegistrationListProvider $provider,
        Request $request,
    ): Response {
        /**  @var RegistrationFilter $filter */
        $filter = $provider->getHydratedDto($request->query->all(), RegistrationFilter::class);
        $filterConfig = $provider->getFilterConfig('admin_registration_list');
        if (!$filterConfig) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ListFilterType::class, $filter, [
            'data_class' => $filterConfig->getDataClass(),
            'fields' => $filterConfig->getFields(),
            'advanced_fields' => $filterConfig->getAdvancedFields(),
            'event_subscriber' => $filterConfig->getEventSubscriber(),
        ]);

        $form->handleRequest($request);

        return $this->render('registration/admin/list.html.twig', [
            'form' => $form->createView(),
            'list' => $provider->getCollection(
                $filter,
                $filterConfig,
                $request->attributes->get('_route'),
                $request->query->getInt('page', 1),
            ),
        ]);
    }

    #[Route('/export/inscription', name: 's_export', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminRegistrationsExport(
        GetRegistrationsFiltered $getRegistrationsFiltered,
        RegistrationListProvider $provider,
        Request $request
    ): StreamedResponse {
        /**  @var RegistrationFilter $filter */
        $filter = $provider->getHydratedDto($request->query->all(), RegistrationFilter::class);

        $response = new StreamedResponse(function() use ($provider, $filter) {
            $provider->streamExportContent($filter);
        });
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export_inscriptions.csv"');

        return $response;
    }

    #[Route('/emails/inscriptions', name: 's_email_to_clipboard', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminEmailRegistrations(
        RegistrationListProvider $provider,
        Request $request
    ): JsonResponse {
        /**  @var RegistrationFilter $filter */
        $filter = $provider->getHydratedDto($request->query->all(), RegistrationFilter::class);

        return new JsonResponse($provider->copyEmailListToClipboard($filter));    }

    #[Route('/inscription/autocomplete', name: '_autocomplete', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function memberAutocomplete(
        RegistrationListProvider $provider,
        Request $request
    ): JsonResponse {
        /**  @var RegistrationFilter $filter */
        $filter = $provider->getHydratedDto($request->query->all(), RegistrationFilter::class);

        return new JsonResponse(['results' => $provider->getAutocompleteChoices($filter)]);
    }
}
