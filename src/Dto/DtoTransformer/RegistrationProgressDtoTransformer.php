<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\Licence;
use App\Entity\RegistrationStep;
use App\Service\ParameterService;
use App\Dto\RegistrationProgressDto;
use App\Repository\RegistrationChangeRepository;

class RegistrationProgressDtoTransformer
{
    public function __construct(
        private RegistrationStepDtoTransformer $registrationStepDtoTransformer,
        private UserDtoTransformer $userDtoTransformer,
        private RegistrationChangeRepository $registrationChangeRepository,
        private ParameterService $parameterService,
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
        $changes = (UserType::FORM_OVERVIEW === $currentRegistrationStep->getForm()) ? $this->getChanges($user, $season) : null;
        $userDto = $this->userDtoTransformer->fromEntity($user, $changes);
        $registrationProgressDto->user = $userDto;
        $registrationProgressDto->current = $this->registrationStepDtoTransformer->fromEntity($currentRegistrationStep, $user, $userDto, $step, registrationStep::RENDER_VIEW);
        $registrationProgressDto->season = $season;

        $registrationProgressDto->redirecToRoute = $this->validate($registrationProgressDto);
        
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
            ];
        }
        return $progressBar;
    }

    private function getChanges(User $user, int $season): array
    {
        return $this->registrationChangeRepository->findBySeason($user, $season);
    }

    private function validate(RegistrationProgressDto $progress): ?string
    {
        if (!$this->parameterService->getParameterByName('NEW_SEASON_RE_REGISTRATION_ENABLED') && $progress->user->prevLicence) {
            return 'unregistrable_new_saison';
        }
        if (Licence::STATUS_IN_PROCESSING < $progress->user->lastLicence->status && UserType::FORM_REGISTRATION_FILE !== $progress->current->form) {
            return 'registration_existing';
        }

        return null;
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
