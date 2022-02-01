<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\RegistrationStep;
use App\Form\UserType;
use Symfony\Component\Form\FormInterface;

class RegistrationStepViewModel extends AbstractViewModel
{
    public ?FormInterface $formObject;

    public ?string $template;

    public ?string $class;

    public null|string|array $content;

    public ?string $filename;

    public ?string $title;

    public ?int $form;

    public static function fromRegistrationStep(
        RegistrationStep $registrationStep,
        array $services,
        ?UserViewModel $user,
        int $step,
        int $render,
        ?string $class = null
    ) {
        $registrationStepView = new self();
        $registrationStepView->class = $class;
        $registrationStepView->title = $registrationStep->getTitle();
        $registrationStepView->form = $registrationStep->getForm();
        $registrationStepView->filename = $registrationStep->getFilename();
        if (null !== $step) {
            $registrationStepView->formObject = $registrationStepView->getForm($registrationStep, $user, $step, $services);
            $registrationStepView->template = $registrationStepView->getTemplate($registrationStep);
        }
        if (null !== $user) {
            $registrationStepView->content = $services['replaceKeywordsService']->replace($user, $registrationStep->getContent(), $render);
        } else {
            $registrationStepView->content = $registrationStep->getContent();
        }

        return $registrationStepView;
    }

    private function getForm(RegistrationStep $registrationStep, ?UserViewModel $user, int $step, $services): ?FormInterface
    {
        $form = null;
        $currentSeason = $services['licenceService']->getCurrentSeason();
        $seasonLicence = $user->entity->getSeasonLicence($currentSeason);
        $formFactory = $services['formFactory'];
        $router = $services['router'];
        $route = (null === $user->entity->getId()) ? 'registration_form' : 'user_registration_form';
        if (null !== $registrationStep->getForm() && UserType::FORM_REGISTRATION_DOCUMENT !== $registrationStep->getForm()) {
            $form = $formFactory->create(UserType::class, $user->entity, [
                'attr' => [
                    'action' => $router->generate($route, [
                        'step' => $step,
                    ]),
                ],
                'current' => $registrationStep,
                'is_kinship' => UserType::FORM_KINSHIP === $registrationStep->getForm(),
                'category' => $seasonLicence->getCategory(),
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
}
