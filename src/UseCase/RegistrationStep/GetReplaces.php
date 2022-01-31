<?php

declare(strict_types=1);

namespace App\UseCase\RegistrationStep;

use App\Entity\RegistrationStep;
use App\Entity\User;
use App\ViewModel\UserPresenter;
use DateTime;
use Symfony\Component\Form\FormInterface;

class GetReplaces
{
    public function __construct(
        private UserPresenter $presenter
    ) {
    }

    public function execute(RegistrationStep $registrationStep, User $user, FormInterface $form): array
    {
        $this->presenter->present($user);
        $user = $this->presenter->viewModel();
        $today = new DateTime();

        return [
            '{{ date }}' => $today->format('d/m/Y'),
            '{{ nom_prenom_parent }}' => $user->getFullName(),
            '{{ date_naissance_parent }}' => $user->getBirthDate(),
            '{{ nom_prenom_enfant }}' => $user->getFullNameChildren(),
            '{{ date_naissance_enfant }}' => $user->getBirthDateChildren(),
            '{{ saut_page }}' => '<br>',
        ];
    }
}
