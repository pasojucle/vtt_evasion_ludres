<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\RegistrationProgressDto;
use App\Entity\RegistrationStep;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\HistoryRepository;
use App\Repository\LicenceRepository;
use App\Service\ParameterService;

class RegistrationProgressDtoTransformer
{
    public function __construct(
        private RegistrationStepDtoTransformer $registrationStepDtoTransformer,
        private UserDtoTransformer $userDtoTransformer,
        private HistoryRepository $historyRepository,
        private ParameterService $parameterService,
        private LicenceRepository $licenceRepository,
    ) {
    }

    public function fromEntities(array $registrationSteps, int $step, User $user, int $season): RegistrationProgressDto
    {
        $registrationProgressDto = new RegistrationProgressDto();
        $registrationProgressDto->currentIndex = $step - 1;
        $registrationProgressDto->prevStep = (1 < $step) ? $step - 1 : null;
        $registrationProgressDto->nextStep = ($step < count($registrationSteps)) ? $step + 1 : null;
        $registrationProgressDto->progressBar = $this->getProgressBar($registrationSteps, $registrationProgressDto);

        /** @var RegistrationStep $currentRegistrationStep */
        $currentRegistrationStep = $registrationSteps[$registrationProgressDto->currentIndex];
        $histories = (UserType::FORM_OVERVIEW === $currentRegistrationStep->getForm()) ? $this->getChanges($user, $season) : null;
        $userDto = $this->userDtoTransformer->fromEntity($user, $histories);
        $registrationProgressDto->user = $userDto;
        $registrationProgressDto->current = $this->registrationStepDtoTransformer->fromEntity($currentRegistrationStep, $user, $userDto, $step, registrationStep::RENDER_VIEW);
        $registrationProgressDto->season = $season;

        $registrationProgressDto->redirecToRoute = $this->validate($registrationProgressDto, $user);
        
        return $registrationProgressDto;
    }

    private function getProgressBar(array $registrationSteps, RegistrationProgressDto $registrationProgressDto): array
    {
        $progressBar = [];
        /** @var RegistrationStep $registrationStep */
        foreach ($registrationSteps as $index => $registrationStep) {
            $class = null;
            if ($index < $registrationProgressDto->currentIndex) {
                $class = 'is-done';
            }
            if ($index === $registrationProgressDto->currentIndex) {
                $class = 'current';
            }
            $progressBar[$index] = [
                'title' => $registrationStep->getTitle(),
                'class' => $class,
                'overviewTemplate' => $this->getOverviewTemplate($registrationStep->getForm()),
                'step' => $index + 1,
            ];
        }

        return $progressBar;
    }

    private function getChanges(User $user, int $season): array
    {
        return $this->historyRepository->findBySeason($user, $season);
    }

    private function validate(RegistrationProgressDto $progress, User $user): ?string
    {
        if (!$this->parameterService->getParameterByName('NEW_SEASON_RE_REGISTRATION_ENABLED') && $this->isAlreadyBeenRegistered($user)) {
            return 'unregistrable_new_saison';
        }
        if ($progress->user->lastLicence->state['value']->isRegistered() && UserType::FORM_REGISTRATION_FILE !== $progress->current->form) {
            return 'registration_existing';
        }

        return null;
    }

    private function isAlreadyBeenRegistered(User $user): bool
    {
        if (!$user->getId()) {
            return false;
        }
        
        return !empty($this->licenceRepository->findByUserAndPeriod($user, 5));
    }

    private function getOverviewTemplate(int $form): ?string
    {
        if (array_key_exists($form, $this->getRegistrationDocumentForms())) {
            return sprintf('registration/form/overviews/%s.html.twig', $this->getRegistrationDocumentForms()[$form]);
        }

        return null;
    }

    private function getRegistrationDocumentForms(): array
    {
        return [
            UserType::FORM_MEMBER => 'member',
            UserType::FORM_KINSHIP => 'kindship',
            UserType::FORM_HEALTH => 'health',
            UserType::FORM_APPROVAL => 'approval',
            UserType::FORM_LICENCE_COVERAGE => 'coverage',
            UserType::FORM_REGISTRATION_DOCUMENT => null,
        ];
    }
}
