<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Entity\Licence;
use App\Entity\Respondent;
use App\Entity\User;
use App\Service\MailerService;
use App\ViewModel\BikeRideViewModel;
use App\ViewModel\UserViewModel;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class EditSession
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerService $mailerService,
    ) {
    }

    public function execute(FormInterface $form, UserViewModel $user, BikeRideViewModel $bikeRide): void
    {
        $data = $form->getData();
        $user->entity->addSession($data['session']);

        $this->answerTheSurvey($data, $user->entity, $bikeRide);
        $this->confirmationRegistration($user, $bikeRide);

        $this->entityManager->persist($data['session']);
        $this->entityManager->flush();
    }

    private function answerTheSurvey(array $data, User $user, BikeRideViewModel $bikeRide): void
    {
        if (array_key_exists('responses', $data) && !empty($data['responses']['surveyResponses'])) {
            foreach ($data['responses']['surveyResponses'] as $response) {
                if (!$bikeRide->entity->getSurvey()->isAnonymous()) {
                    $response->setUser($user);
                }
                $this->entityManager->persist($response);
            }
            $now = new DateTime();

            $respondent = new Respondent();
            $respondent->setUser($user)
                ->setSurvey($bikeRide->entity->getSurvey())
                ->setCreatedAt($now)
            ;
            $this->entityManager->persist($respondent);
        }
    }

    private function confirmationRegistration(UserViewModel $user, BikeRideViewModel $bikeRide): void
    {
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
