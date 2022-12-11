<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Entity\BikeRide;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\Respondent;
use App\Entity\User;
use App\Service\MailerService;
use App\ViewModel\BikeRidePresenter;
use App\ViewModel\UserPresenter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class ConfirmationSession
{
    public function __construct(
        private MailerService $mailerService,
        private UserPresenter $userPresenter,
        private BikeRidePresenter $bikeRidePresenter
    ) {
    }

    public function execute(User $user, BikeRide $bikeRide): void
    {
        $this->userPresenter->present($user);
        $this->bikeRidePresenter->present($bikeRide);
        $user = $this->userPresenter->viewModel();
        $bikeRide = $this->bikeRidePresenter->viewModel();
        $content = (Licence::CATEGORY_MINOR === $user->seasonLicence->category)
            ? 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_MINOR'
            : (Level::TYPE_FRAME === $user->level->type ? 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_FRAMER' : 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_ADULT');

        $this->mailerService->sendMailToMember([
            'name' => $user->member->name,
            'firstName' => $user->member->firstName,
            'email' => $user->mainEmail,
            'subject' => 'Confirmation d\'inscription à une sortie',
            'bikeRideTitleAndPeriod' => $bikeRide->title . ' du ' . $bikeRide->period,
        ], $content);
    }
}
