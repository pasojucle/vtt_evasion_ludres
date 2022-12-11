<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Entity\BikeRide;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\Respondent;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class AddSession
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ConfirmationSession $confirmationSession
    ) {
    }

    public function execute(FormInterface $form, User $user, BikeRide $bikeRide): void
    {
        $data = $form->getData();
        $user->addSession($data['session']);

        $this->answerTheSurvey($data, $user, $bikeRide);
        $this->confirmationSession->execute($user, $bikeRide);

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
        $content = (Licence::CATEGORY_MINOR === $user->seasonLicence->category)
            ? 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_MINOR'
            : (Level::TYPE_FRAME === $user->level->type ? 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_FRAME' : 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_ADULT');

        $this->mailerService->sendMailToMember([
            'name' => $user->member->name,
            'firstName' => $user->member->firstName,
            'email' => $user->mainEmail,
            'subject' => 'Confirmation d\'inscription à une sortie',
            'bikeRideTitleAndPeriod' => $bikeRide->title . ' du ' . $bikeRide->period,
        ], $content);
    }
}
