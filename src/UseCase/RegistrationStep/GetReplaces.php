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
        // $containFormRow = false;
        // if (UserType::FORM_HEALTH_QUESTION === $registrationStep->getForm()) {
        //     $healthQuestions = $form->get('health')->get('healthQuestions');
        //     foreach($healthQuestions as $key => $healthQuestion) {
        //         $containFormRow = true;
        //         $index = '{{ question_'.($key + 1).' }}';
        //         $replaces[$index] = $healthQuestion->createView();
        //     }
        // }
        return $replaces;
    }
}