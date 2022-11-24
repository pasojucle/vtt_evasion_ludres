<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Entity\BikeRide;
use App\Entity\Licence;
use App\Entity\Respondent;
use App\Entity\User;
use App\Service\MailerService;
use App\ViewModel\BikeRidePresenter;
use App\ViewModel\UserPresenter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class EditSession
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerService $mailerService,
        private UserPresenter $userPresenter,
        private BikeRidePresenter $bikeRidePresenter
    ) {
    }

    public function execute(FormInterface $form, User $user, BikeRide $bikeRide): void
    {
        $data = $form->getData();
        $user->addSession($data['session']);

        $this->answerTheSurvey($data, $user, $bikeRide);
        $this->confirmationRegistration($user, $bikeRide);

        $this->entityManager->persist($data['session']);
        $this->entityManager->flush();
    }

    private function answerTheSurvey(array $data, User $user, BikeRide $bikeRide): void
    {
        if (array_key_exists('responses', $data) && !empty($data['responses']['surveyResponses'])) {
            foreach ($data['responses']['surveyResponses'] as $response) {
                if (!$bikeRide->getSurvey()->isAnonymous()) {
                    $response->setUser($user);
                }
                $this->entityManager->persist($response);
            }
            $now = new DateTime();

            $respondent = new Respondent();
            $respondent->setUser($user)
                ->setSurvey($bikeRide->getSurvey())
                ->setCreatedAt($now)
            ;
            $this->entityManager->persist($respondent);
        }
    }

    private function confirmationRegistration(User $user, BikeRide $bikeRide): void
    {
        $this->userPresenter->present($user);
        $this->bikeRidePresenter->present($bikeRide);
        $user = $this->userPresenter->viewModel();
        $bikeRide = $this->bikeRidePresenter->viewModel();
        if (Licence::CATEGORY_MINOR === $user->seasonLicence->category) {
            $this->mailerService->sendMailToMember([
                'name' => $user->member->name,
                'firstName' => $user->member->firstName,
                'email' => $user->mainEmail,
                'subject' => 'Confirmation d\'inscription à une sortie',
                'bikeRideTitleAndPeriod' => $bikeRide->title . ' du ' . $bikeRide->period,
            ], 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION');
        }
    }
}
