<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Dto\DtoTransformer\RegistrationStepDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\RegistrationStepDto;
use App\Dto\UserDto;
use App\Entity\RegistrationStep;
use App\Entity\User;
use App\Repository\ContentRepository;
use App\Repository\HistoryRepository;
use App\Repository\MembershipFeeRepository;
use App\Repository\RegistrationStepRepository;
use App\Service\HealthService;
use App\Service\PdfService;
use App\Service\ProjectDirService;
use App\Service\SeasonService;
use DateTime;
use Twig\Environment;
use ZipArchive;

class GetRegistrationFile
{
    private array $registrationDocumentSteps = [];
    private array $files = [];
    private array $allmembershipFee;

    public function __construct(
        private MembershipFeeRepository $membershipFeeRepository,
        private PdfService $pdfService,
        private UserDtoTransformer $userDtoTransformer,
        private RegistrationStepDtoTransformer $registrationStepDtoTransformer,
        private Environment $twig,
        private HealthService $healthService,
        private SeasonService $seasonService,
        private RegistrationStepRepository $registrationStepRepository,
        private ContentRepository $contentRepository,
        private HistoryRepository $historyRepository,
        private ProjectDirService $projectDir,
    ) {
    }

    public function execute(User $user): array
    {
        $season = $this->seasonService->getCurrentSeason();
        $lastLicence = $user->getLastLicence();
        $category = $lastLicence->getCategory();
        $steps = $this->registrationStepRepository->findByCategoryAndFinal($category, $lastLicence->isFinal(), RegistrationStep::RENDER_FILE);

        $this->allmembershipFee = $this->membershipFeeRepository->findAll();
        $this->healthService->getHealthSwornCertifications($user);

        $histories = $this->historyRepository->findBySeason($user, $season);
        $userDto = $this->userDtoTransformer->fromEntity($user, $histories);

        foreach ($steps as $step) {
            $step = $this->registrationStepDtoTransformer->fromEntity($step, $user, $userDto, 1, RegistrationStep::RENDER_FILE);
            if (null !== $step->pdfFilename) {
                $this->files[$step->outputFilename][] = [
                    'filename' => $step->pdfPath,
                    'form' => $step->form,
                    'final_render' => $step->finalRender,
                ];
            }
            if (array_key_exists($step->form, $step->registrationDocumentForms)) {
                $this->registrationDocumentSteps[$step->form] = $step->content;
            } elseif (null !== $step->content) {
                $this->addRegistrationStep($step, $userDto);
            }
        }
    
        $this->addRegistrationDocument($user, $userDto);

        $registrationFiles = [];
        foreach (RegistrationStepDto::OUTPUT_FILENAMES as $key => $outputFilename) {
            $fileTmp = $this->projectDir->path('tmp', $outputFilename);
            $registrationFiles[] = $this->pdfService->joinPdf($this->files[$key], $user, $key, $fileTmp);
        }
        
        return $registrationFiles;
    }

    private function addRegistrationStep(RegistrationStepDto $step, UserDto $userDto)
    {
        $html = null;
        if (null !== $step->form) {
            $form = $step->formObject;
            $html = $this->twig->render('registration/registrationPdf.html.twig', [
                'user' => $userDto,
                'all_membership_fee' => $this->allmembershipFee,
                'membership_fee_content' => $this->contentRepository->findOneByRoute('registration_membership_fee')?->getContent(),
                'progress' => ['current' => $step],
                'form' => $form->createView(),
                'media' => RegistrationStep::RENDER_FILE,
                'template' => $step->template,
            ]);
        } else {
            $html = $step->content;
        }

        if (null !== $html) {
            $pdfFilepath = $this->pdfService->makePdf($html, $step->title);
            $this->files[$step->outputFilename][] = [
                'filename' => $pdfFilepath,
                'form' => $step->form,
            ];
        }
    }

    private function addRegistrationDocument(User $userEntity, UserDto $userDto)
    {
        if (!empty($this->registrationDocumentSteps)) {
            $registration = $this->twig->render('registration/registrationPdf.html.twig', [
                'user' => $userDto,
                'user_entity' => $userEntity,
                'registration_document_steps' => $this->registrationDocumentSteps,
                'licence' => $userDto->lastLicence,
                'media' => RegistrationStep::RENDER_FILE,
            ]);
            $pdfFilepath = $this->pdfService->makePdf($registration, 'registration_temp');

            array_unshift($this->files[RegistrationStepDto::OUTPUT_FILENAME_CLUB], [
                'filename' => $pdfFilepath,
                'form' => null,
            ]);
        }
    }
}
