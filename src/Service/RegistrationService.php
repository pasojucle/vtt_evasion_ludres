<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Licence;
use App\Entity\User;
use App\Form\UserType;

class RegistrationService
{
    // private int $season;

    // public function __construct(
    //     private SeasonService $seasonService,
    //     private UserDtoTransformer $userDtoTransformer
    // ) {
    //     $this->season = $this->seasonService->getCurrentSeason();
    // }

    // public function getTemplate(int $form): ?string
    // {
    //     if (UserType::FORM_REGISTRATION_DOCUMENT === $form) {
    //         return null;
    //     }

    //     return 'registration/form/' . str_replace('form.', '', UserType::FORMS[$form]) . '.html.twig';
    // }

    // public function getSeason(): int
    // {
    //     return $this->season;
    // }

    // public function getReplaces(User $user)
    // {
    //     $user = $this->userDtoTransformer->fromEntity($user);

    //     return [
    //         '{{ prenom_nom }}' => $user->ffctLicence->fullName,
    //         '{{ prenom_nom_enfant }}' => $user->ffctLicence->fullNameChildren,
    //     ];
    // }

    // public function isAllreadyRegistered(?User $user): bool
    // {
    //     $isAllreadyRegistered = false;

    //     if (null !== $user) {
    //         $licence = $user->getSeasonLicence($this->season);
    //         if (null !== $licence) {
    //             if ($licence->isFinal() && Licence::STATUS_IN_PROCESSING < $licence->getStatus()) {
    //                 $isAllreadyRegistered = true;
    //             }
    //             if (!$licence->isFinal() && 1 > count(${$user}->getSessionsDone())) {
    //                 $isAllreadyRegistered = true;
    //             }
    //         }
    //     }

    //     return $isAllreadyRegistered;
    // }

    // private function getForm(RegistrationStep $registrationStep, bool $isKinship, ?int $category, int $step): ?FormInterface
    // {
    //     $form = null;

    //     if (null !== $registrationStep->getForm() && UserType::FORM_REGISTRATION_DOCUMENT !== $registrationStep->getForm()) {
    //         $form = $this->formFactory->create(UserType::class, $this->user, [
    //             'attr' => [
    //                 'action' => $this->router->generate('registration_form', [
    //                     'step' => $step,
    //                 ]),
    //             ],
    //             'current' => $registrationStep,
    //             'is_kinship' => $isKinship,
    //             'category' => $category,
    //             'season_licence' => $this->seasonLicence,
    //         ]);
    //     }

    //     return $form;
    // }
}
