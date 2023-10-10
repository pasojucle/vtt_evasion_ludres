<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\UseCase\Registration\GetRegistrationsFiltered;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin', name:'admin_registration')]
class RegistrationController extends AbstractController
{
    #[Route('/inscriptions/{filtered}', name: 's', methods: ['GET', 'POST'], defaults:['filtered' => 0])]
    #[IsGranted('USER_LIST')]
    public function adminRegistrations(
        GetRegistrationsFiltered $getRegistrationsFiltered,
        Request $request,
        bool $filtered
    ): Response {
        return $this->render(
            'user/admin/registrations.html.twig',
            $getRegistrationsFiltered->list($request, $filtered)
        );
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
