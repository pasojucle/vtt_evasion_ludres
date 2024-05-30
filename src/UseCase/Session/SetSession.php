<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Respondent;
use App\Entity\Session;
use App\Entity\User;
use App\Service\CacheService;
use App\Service\ModalWindowService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\Form\FormInterface;

class SetSession
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ConfirmationSession $confirmationSession,
        private BikeRideDtoTransformer $bikeRideDtoTransformer,
        private ModalWindowService $modalWindowService,
        private CacheService $cacheService,
    ) {
    }

    public function add(FormInterface $form, User $user, BikeRide $bikeRide): void
    {
        $session = $form->get('session')->getData();
        $user->addSession($session);
        $responses = ($form->has('responses')) ? $form->get('responses')->getData() : null;
        if ($responses && !empty($surveyResponses = $responses['surveyResponses'])) {
            $this->addSurveyResponses($surveyResponses, $user, $bikeRide);
        }
        $this->confirmationSession->execute($session);
        $this->cacheService->deleteCacheIndex($session->getCluster());
        $this->entityManager->persist($session);
        $this->entityManager->flush();

        $bikeRideDto = $this->bikeRideDtoTransformer->fromEntity($bikeRide);
        $content = ($session->getAvailability())
            ? '<p>Votre disponibilité à la sortie %s du %s a bien été prise en compte.</p><p> En cas de changement, il est impératif de se modifier sa disponibilité (voir dans Mon programme perso et faire "Modifier)"</p>'
            : '<p>Votre inscription à la sortie %s du %s a bien été prise en compte.</p><p> Si vous ne pouvez plus participez pas à cette sortie, il est impératif de se désinsrire (voir dans Mon programme perso et faire "Se désinscrire)"</p>';
        $this->modalWindowService->addToModalWindow('Inscription à une sortie', sprintf($content, $bikeRideDto->title, $bikeRideDto->period));
    }

    public function edit(FormInterface $form, Session $session): void
    {
        $session = $form->get('session')->getData();
        $responses = ($form->has('responses')) ? $form->get('responses')->getData() : null;
        if ($responses && !empty($surveyResponses = $responses['surveyResponses'])) {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            $entityState = UnitOfWork::STATE_MANAGED;
            foreach ($surveyResponses as $response) {
                $responseEntityState = $unitOfWork->getEntityState($response);
                if (UnitOfWork::STATE_MANAGED < $responseEntityState) {
                    $entityState = $responseEntityState;
                    break;
                }
            }
            if (UnitOfWork::STATE_MANAGED < $entityState) {
                $this->addSurveyResponses($surveyResponses, $session->getUser(), $session->getCluster()->getBikeRide());
            }
        }
        $this->entityManager->flush();
        $this->cacheService->deleteCacheIndex($session->getCluster());
        $this->confirmationSession->execute($session);
    }

    private function addSurveyResponses(array $surveyResponses, User $user, BikeRide $bikeRide): void
    {
        foreach ($surveyResponses as $response) {
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
