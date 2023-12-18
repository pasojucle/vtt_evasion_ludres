<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Respondent;
use App\Entity\Session;
use App\Entity\User;
use App\Service\ModalWindowService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class AddSession
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ConfirmationSession $confirmationSession,
        private BikeRideDtoTransformer $bikeRideDtoTransformer,
        private ModalWindowService $modalWindowService,
    ) {
    }

    public function execute(FormInterface $form, User $user, BikeRide $bikeRide): void
    {
        $data = $form->getData();
        $user->addSession($data['session']);

        $this->answerTheSurvey($data, $user, $bikeRide);
        $this->confirmationSession->execute($data['session']);

        $this->entityManager->persist($data['session']);
        $this->entityManager->flush();

        $bikeRideDto = $this->bikeRideDtoTransformer->fromEntity($bikeRide);
        $content = ($data['session']->getAvailability())
            ? '<p>Votre disponibilité à la sortie %s du %s a bien été prise en compte.</p><p> En cas de changement, il est impératif de se modifier sa disponibilité (voir dans Mon programme perso et faire "Modifier)"</p>'
            : '<p>Votre inscription à la sortie %s du %s a bien été prise en compte.</p><p> Si vous ne pouvez plus participez pas à cette sortie, il est impératif de se désinsrire (voir dans Mon programme perso et faire "Se désinscrire)"</p>';
        $this->modalWindowService->addToModalWindow('Inscription à une sortie', sprintf($content, $bikeRideDto->title, $bikeRideDto->period));
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
}
