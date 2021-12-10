<?php

namespace App\UseCase\RegistrationStep;

use DateTime;
use App\Entity\User;
use App\Entity\RegistrationStep;
use App\Form\UserType;
use App\ViewModel\UserPresenter;
use Symfony\Component\Form\FormInterface;

class GetReplaces
{
    public function __construct(
        private UserPresenter $presenter
    )
    {
    }

    public function execute(RegistrationStep $registrationStep, User $user,FormInterface $form): array
    {
        $this->presenter->present($user);
        $user = $this->presenter->viewModel();
        $today = new DateTime();
        $replaces = [
            '{{ date }}' => $today->format('d/m/Y'),
            '{{ nom_prenom_parent }}' => $user->getFullName(),
            '{{ date_naissance_parent }}' => $user->getBirthDate(),
            '{{ nom_prenom_enfant }}' => $user->getFullNameChildren(),
            '{{ date_naissance_enfant }}' => $user->getBirthDateChildren(),
            '{{ saut_page }}' => '<br>',
        ];
        return $replaces;
    }
}