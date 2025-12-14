<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\UserDto;
use App\Entity\User;
use App\Form\UserType;
use App\Dto\RegistrationStepDto;
use App\Entity\RegistrationStep;
use App\Service\ProjectDirService;
use App\Entity\Enum\DisplayModeEnum;
use App\Service\ReplaceKeywordsService;
use App\Entity\Enum\RegistrationFormEnum;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationStepDtoTransformer
{
    public function __construct(
        private RequestStack $requestStack,
        public UrlGeneratorInterface $router,
        private FormFactoryInterface $formFactory,
        private ReplaceKeywordsService $replaceKeywordsService,
        private ProjectDirService $projectDir
    ) {
    }

    public function fromEntity(
        ?RegistrationStep $registrationStep,
        ?User $user = null,
        ?UserDto $userDto = null,
        ?int $step = null,
        ?DisplayModeEnum $displayMode = DisplayModeEnum::SCREEN,
    ): RegistrationStepDto {
        $registrationStepDto = new RegistrationStepDto();
        if ($registrationStep) {
            $registrationStepDto->title = $registrationStep->getTitle();
            $registrationStepDto->form = $registrationStep->getForm();
            $registrationStepDto->outputFilename = ($registrationStep->isPersonal()) 
                ? RegistrationStepDto::OUTPUT_FILENAME_PERSONAL 
                : RegistrationStepDto::OUTPUT_FILENAME_CLUB;
            $filename = $registrationStep->getFilename();
            $registrationStepDto->pdfFilename = $filename;
            $registrationStepDto->pdfRelativePath = ($filename) ? $this->projectDir->dir('', 'files', $filename) : null;
            $registrationStepDto->pdfPath = ($filename) ? $this->projectDir->path('files_directory_path', $filename) : null;
            $registrationStepDto->yearlyDisplayMode = $registrationStep->getYearlyDisplayMode();
        }

        if (null !== $step) {
            $registrationStepDto->formObject = $this->getForm($registrationStep, $user, $userDto, $step);
            $registrationStepDto->template = $this->getTemplate($registrationStep);
            $registrationStepDto->content = $this->getContent($userDto, $displayMode, $registrationStep->getContent());
            $registrationStepDto->overviewTemplate = (DisplayModeEnum::SCREEN === $displayMode)
                ? $this->getScreenOverviewTemplate($registrationStep->getForm())
                : $this->getFileOverviewTemplate($registrationStep->getForm());
            $registrationStepDto->hasRequiredFields = $this->getHasRequiredFields($registrationStepDto);
        }

        return $registrationStepDto;
    }

    private function getForm(RegistrationStep $registrationStep, ?User $user, UserDto $userDto, int $step): ?FormInterface
    {
        $form = null;

        $seasonLicence = ($userDto->lastLicence->isSeasonLicence) ? $userDto->lastLicence : null;

        $route = ('user_registration_form' === $this->requestStack->getCurrentRequest()->get('_route')) ? 'user_registration_form' : 'registration_form';

        if (null !== $registrationStep->getForm() && RegistrationFormEnum::REGISTRATION_DOCUMENT !== $registrationStep->getForm()) {
            $form = $this->formFactory->create(UserType::class, $user, [
                'attr' => [
                    'action' => $this->router->generate($route, [
                        'step' => $step,
                    ]),
                ],
                'current' => $registrationStep,
                'is_gardian' => RegistrationFormEnum::GARDIANS === $registrationStep->getForm(),
                'category' => $seasonLicence?->category,
                'season_licence' => $seasonLicence,
            ]);
        }

        return $form;
    }

    private function getTemplate(RegistrationStep $registrationStep): ?string
    {
        $form = $registrationStep->getForm();
        if (RegistrationFormEnum::REGISTRATION_DOCUMENT === $form) {
            return null;
        }

        return sprintf('registration/form/%s.html.twig', $form->value);
    }

    private function getContent(UserDto $userDto, DisplayModeEnum $displayMode, ?string $content): null|string|array
    {
        return $this->replaceKeywordsService->replace($content, $userDto, $displayMode);
    }

    private function getScreenOverviewTemplate(RegistrationFormEnum $form): ?string
    {
        $filename = sprintf('registration/form/overviews/%s.html.twig', $form->value);
        if (file_exists($this->projectDir->path('templates',$filename)) || RegistrationFormEnum::REGISTRATION_DOCUMENT === $form) {
            return $filename;
        }

        return null;
    }

    private function getFileOverviewTemplate(RegistrationFormEnum $form): ?string
    {
        $filename = sprintf('registration/form/pdf/%s.html.twig', $form->value);
        if (file_exists($this->projectDir->path('templates',$filename)) || RegistrationFormEnum::REGISTRATION_DOCUMENT === $form) {
            return $filename;
        }

        return null;
    }

    public function getHasRequiredFields(RegistrationStepDto $registrationStep): bool
    {
        $hasRequiredFields = false;
        if ($registrationStep->formObject) {
            foreach ($registrationStep->formObject->all() as $form) {
                $reqired = $form->getConfig()->getRequired();
                if ($reqired) {
                    $hasRequiredFields = true;
                }
            }
        }

        return $hasRequiredFields;
    }
}
