<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\RegistrationStepDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\RegistrationStep;
use App\Entity\User;
use App\Repository\RegistrationChangeRepository;
use App\Repository\RegistrationStepRepository;
use App\Service\PdfService;
use App\Service\SeasonService;
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
        RegistrationChangeRepository $registrationChangeRepository,
        SeasonService $seasonService,
        RegistrationStepDtoTransformer $registrationStepDtoTransformer,
        PdfService $pdfService,
        UserDtoTransformer $userDtoTransformer,
        User $user
    ) {
        $changes = $registrationChangeRepository->findBySeason($user, $seasonService->getCurrentSeason());
        $userDto = $userDtoTransformer->fromEntity($user, $changes);

        $coverageStep = $registrationStepRepository->findCoverageStep();

        $step = $registrationStepDtoTransformer->fromEntity($coverageStep, $user, $userDto, 1, RegistrationStep::RENDER_FILE);
        $files = [];
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
