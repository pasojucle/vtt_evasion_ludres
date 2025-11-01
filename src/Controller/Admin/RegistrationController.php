<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\MessageService;
use App\Service\ParameterService;
use App\UseCase\Registration\GetRegistrationsFiltered;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name:'admin_registration')]
class RegistrationController extends AbstractController
{
    #[Route('/inscriptions/{filtered}', name: 's', methods: ['GET', 'POST'], defaults:['filtered' => 0])]
    #[IsGranted('USER_LIST')]
    public function adminRegistrations(
        GetRegistrationsFiltered $getRegistrationsFiltered,
        ParameterService $parameterService,
        MessageService $messageService,
        Request $request,
        bool $filtered
    ): Response {
        $params = $getRegistrationsFiltered->list($request, $filtered);

        $params['settings'] = [
            'parameters' => $parameterService->getParametersByParameterGroupName('REGISTRATION'),
            'routes' => [
                ['name' => 'admin_registration_step_list', 'label' => 'Étapes des inscriptions'],
            ],
            'messages' => $messageService->getMessagesBySectionName('REGISTRATION'),
        ];
        return $this->render('user/admin/registrations.html.twig', $params);
    }

    #[Route('/export/inscription', name: 's_export', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminRegistrationsExport(
        GetRegistrationsFiltered $getRegistrationsFiltered,
        Request $request
    ): Response {
        return $getRegistrationsFiltered->export($request);
    }

    #[Route('/emails/inscriptions', name: 's_email_to_clipboard', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminEmailRegistrations(
        GetRegistrationsFiltered $getRegistrationsFiltered,
        Request $request
    ): JsonResponse {
        return new JsonResponse($getRegistrationsFiltered->emailsToClipboard($request));
    }

    #[Route('/inscription/autocomplete', name: '_autocomplete', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function memberAutocomplete(
        GetRegistrationsFiltered $getRegistrationsFiltered,
        Request $request
    ): JsonResponse {
        return new JsonResponse(['results' => $getRegistrationsFiltered->choices($request->query->all())]);
    }
}
