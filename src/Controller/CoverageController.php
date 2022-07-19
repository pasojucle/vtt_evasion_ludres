<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\RegistrationStep;
use App\Entity\User;
use App\Repository\RegistrationStepRepository;
use App\Service\PdfService;
use App\ViewModel\RegistrationStepPresenter;
use App\ViewModel\UserPresenter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/coverage', name: 'coverage')]
class CoverageController extends AbstractController
{
    #[Route('current/season/{user}', name: '_current_season_edit', methods: ['GET'])]
    public function currentSeasonEdit(
        RegistrationStepRepository $registrationStepRepository,
        RegistrationStepPresenter $registrationStepPresenter,
        PdfService $pdfService,
        UserPresenter $userPresenter,
        User $user
    ) {
        $userPresenter->present($user);

        $coverageStep = $registrationStepRepository->findCoverageStep();

        $registrationStepPresenter->present($coverageStep, $userPresenter->viewModel(), 1, RegistrationStep::RENDER_FILE);
        $step = $registrationStepPresenter->viewModel();
        if (null !== $step->filename) {
            $filename = './files/' . $step->filename;
            $files[] = [
                'filename' => $filename,
                'form' => $step->form,
            ];
        }

        $filename = $pdfService->joinPdf($files, $user);

        $fileContent = file_get_contents($filename);

        $response = new Response($fileContent);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'inscription_vtt_evasion_ludres.pdf'
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }
}
