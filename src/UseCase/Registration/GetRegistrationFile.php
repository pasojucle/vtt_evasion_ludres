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
use App\Repository\MembershipFeeRepository;
use App\Repository\RegistrationChangeRepository;
use App\Repository\RegistrationStepRepository;
use App\Service\HealthService;
use App\Service\LicenceService;
use App\Service\PdfService;
use App\Service\SeasonService;
use DateTime;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class GetRegistrationFile
{
    private User $user;
    private array $registrationDocumentSteps = [];
    private array $files = [];
    private array $allmembershipFee;

    public function __construct(
        private MembershipFeeRepository $membershipFeeRepository,
        private PdfService $pdfService,
        private UserDtoTransformer $userDtoTransformer,
        private RegistrationStepDtoTransformer $registrationStepDtoTransformer,
        private Environment $twig,
        private Security $security,
        private LicenceService $licenceService,
        private HealthService $healthService,
        private SeasonService $seasonService,
        private RegistrationStepRepository $registrationStepRepository,
        private ContentRepository $contentRepository,
        private RequestStack $requestStack,
        private RegistrationChangeRepository $registrationChangeRepository,
    ) {
    }

    public function execute(User $user): string
    {
        $this->user = $user;
        $healthQuestions = null;
        $season = $this->seasonService->getCurrentSeason();
        $seasonLicence = $user->getSeasonLicence($season);
        $category = $seasonLicence->getCategory();
        $steps = $this->registrationStepRepository->findByCategoryAndFinal($category, $seasonLicence->isFinal(), RegistrationStep::RENDER_FILE);

        $this->allmembershipFee = $this->membershipFeeRepository->findAll();
        if ($this->security->getUser() === $user) {
            $today = new DateTime();
            $seasonLicence->setCreatedAt($today);
            $healthQuestions = $this->requestStack->getSession()->get('health_questions');
        }
        if (!$healthQuestions) {
            $formQuestionCount = $this->healthService->getHealthQuestionsCount($this->licenceService->getCategory($user));
            $healthQuestions = $this->healthService->createHealthQuestions($formQuestionCount);
        }

        $user->getHealth()->setHealthQuestions($healthQuestions);
        $changes = $this->registrationChangeRepository->findBySeason($user, $season);
        $userDto = $this->userDtoTransformer->fromEntity($user, $changes);

        foreach ($steps as $step) {
            $step = $this->registrationStepDtoTransformer->fromEntity($step, $user, $userDto, 1, RegistrationStep::RENDER_FILE);
            if (null !== $step->filename) {
                $filename = './files/' . $step->filename;
                $this->files[] = [
                    'filename' => $filename,
                    'form' => $step->form,
                ];
            }
            if (array_key_exists($step->form, $step->registrationDocumentForms)) {
                $this->registrationDocumentSteps[$step->form] = $step->content;
            } elseif (null !== $step->content) {
                $this->addRegistrationStep($step, $userDto);
            }
        }
    

        $this->addRegistrationDocument($userDto);

        $filename = $this->pdfService->joinPdf($this->files, $user);

        return file_get_contents($filename);
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
                'current' => $step,
                'form' => $form->createView(),
                'media' => RegistrationStep::RENDER_FILE,
                'template' => $step->template,
            ]);
        } else {
            $html = $step->content;
        }

        if (null !== $html) {
            $pdfFilepath = $this->pdfService->makePdf($html, $step->title);
            $this->files[] = [
                'filename' => $pdfFilepath,
                'form' => $step->form,
            ];
        }
    }

    private function addRegistrationDocument(UserDto $userDto)
    {
        if (!empty($this->registrationDocumentSteps)) {
            $registration = $this->twig->render('registration/registrationPdf.html.twig', [
                'user' => $userDto,
                'user_entity' => $this->user,
                'registration_document_steps' => $this->registrationDocumentSteps,
                'licence' => $userDto->lastLicence,
                'media' => RegistrationStep::RENDER_FILE,
            ]);
            $pdfFilepath = $this->pdfService->makePdf($registration, 'registration_temp');
            array_unshift($this->files, [
                'filename' => $pdfFilepath,
                'form' => null,
            ]);
        }
    }
}
