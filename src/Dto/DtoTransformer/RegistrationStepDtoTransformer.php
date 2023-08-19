<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\RegistrationStepDto;
use App\Dto\UserDto;
use App\Entity\RegistrationStep;
use App\Entity\User;
use App\Form\UserType;
use App\Service\ReplaceKeywordsService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationStepDtoTransformer
{
    public function __construct(
        private RequestStack $requestStack,
        public UrlGeneratorInterface $router,
        private FormFactoryInterface $formFactory,
        private ReplaceKeywordsService $replaceKeywordsService,
    ) {
    }

    public function fromEntity(
        RegistrationStep $registrationStep,
        ?User $user,
        UserDto $userDto,
        int $step,
        int $render,
        ?string $class = null
    ): RegistrationStepDto {
        $registrationStepDto = new RegistrationStepDto();
        $registrationStepDto->registrationDocumentForms = $this->getRegistrationDocumentForms();
        $registrationStepDto->class = $class;
        $registrationStepDto->title = $registrationStep->getTitle();
        $registrationStepDto->form = $registrationStep->getForm();
        $registrationStepDto->filename = $registrationStep->getFilename();
        if (null !== $step) {
            $registrationStepDto->formObject = $this->getForm($registrationStep, $user, $userDto, $step);
            $registrationStepDto->template = $this->getTemplate($registrationStep);
        }

        $registrationStepDto->content = $this->getContent($userDto, $render, $registrationStep->getContent());

        $registrationStepDto->overviewTemplate = $this->getOverviewTemplate($registrationStep->getForm());

        return $registrationStepDto;
    }

    private function getForm(RegistrationStep $registrationStep, ?User $user, UserDto $userDto, int $step): ?FormInterface
    {
        $form = null;

        $seasonLicence = ($userDto->lastLicence->isSeasonLicence) ? $userDto->lastLicence : null;

        $route = ('user_registration_form' === $this->requestStack->getCurrentRequest()->get('_route')) ? 'user_registration_form' : 'registration_form';

        if (null !== $registrationStep->getForm() && UserType::FORM_REGISTRATION_DOCUMENT !== $registrationStep->getForm()) {
            $form = $this->formFactory->create(UserType::class, $user, [
                'attr' => [
                    'action' => $this->router->generate($route, [
                        'step' => $step,
                    ]),
                ],
                'current' => $registrationStep,
                'is_kinship' => UserType::FORM_KINSHIP === $registrationStep->getForm(),
                'category' => $seasonLicence?->category,
                'season_licence' => $seasonLicence,
            ]);
        }

        return $form;
    }

    private function getTemplate(RegistrationStep $registrationStep): ?string
    {
        $form = $registrationStep->getForm();
        if (UserType::FORM_REGISTRATION_DOCUMENT === $form) {
            return null;
        }

        return 'registration/form/' . str_replace('form.', '', UserType::FORMS[$form]) . '.html.twig';
    }

    private function getContent(UserDto $userDto, int $render, ?string $content): null|string|array
    {
        return (null !== $userDto->id)
             ? $this->replaceKeywordsService->replace($userDto, $content, $render)
            : $content;
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
