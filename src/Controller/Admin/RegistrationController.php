<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\UseCase\Registration\ExportRegistrationsFiltered;
use App\UseCase\Registration\GetRegistrationsFiltered;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin', name:'admin_registration')]
class RegistrationController extends AbstractController
{
    #[Route('/inscriptions/{filtered}', name: 's', methods: ['GET', 'POST'], defaults:['filtered' => 0])]
    public function adminRegistrations(
        GetRegistrationsFiltered $getRegistrationsFiltered,
        Request $request,
        bool $filtered
    ): Response {

        return $this->render(
            'user/admin/registrations.html.twig',
            $getRegistrationsFiltered->execute($request, $filtered)
        );
    }


    #[Route('/export/inscription', name: 's_export', methods: ['GET'])]
    public function adminRegistrationsExport(
        ExportRegistrationsFiltered $exportRegistrationsFiltered,
        Request $request
    ): Response {

        return $exportRegistrationsFiltered->execute($request);
    }
}
