<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\ParameterRepository;
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
        ParameterRepository $parameterRepository,
        Request $request,
        bool $filtered
    ): Response {
        $params = $getRegistrationsFiltered->list($request, $filtered);

        $params['settings'] = [
            'parameters' => $parameterRepository->findByParameterGroupName('REGISTRATION'),
            'routes' => [
                ['name' => 'admin_registration_step_list', 'label' => 'Étapes des inscriptions'],
            ],
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

    #[Route('/assurance/choices', name: '_choices', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function memberChoices(
        GetRegistrationsFiltered $getRegistrationsFiltered,
        Request $request
    ): JsonResponse {
        $query = $request->query->get('q');

        $filters = json_decode($request->query->get('filters'), true);

        return new JsonResponse($getRegistrationsFiltered->choices($filters, $query));
    }
}
