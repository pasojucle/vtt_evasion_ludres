<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Entity\BikeRide;
use App\Entity\Session;
use App\Entity\SurveyResponse;
use App\Entity\User;
use App\Form\SessionType;
use App\Repository\SessionRepository;
use App\Service\SessionService;
use App\UseCase\BikeRide\CreateClusters;
use App\UseCase\BikeRide\IsWritableAvailability;
use App\ViewModel\UserPresenter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class GetFormSession
{
    public array $params;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CreateClusters $createClusters,
        private SessionRepository $sessionRepository,
        private FormFactoryInterface $formFactory,
        private SessionService $sessionService,
        private UserPresenter $userPresenter,
        private IsWritableAvailability $isWritableAvailability
    ) {
    }

    public function execute(User $user, BikeRide $bikeRide): FormInterface
    {
        $clusters = $this->getBikeRideClusters($bikeRide);
        $userSession = $this->getUserSession($bikeRide, $user, $clusters);

        $this->userPresenter->present($user);
        $sessions = (null !== $userSession->getAvailability())
            ? $this->sessionService->getSessionsBytype($bikeRide)
            : $this->sessionService->getSessions($bikeRide);

        $isEndTesting = $this->userPresenter->viewModel()->isEndTesting();

        $form = $this->getForm($userSession, $bikeRide, $clusters, $isEndTesting);

        $this->setParams($form, $bikeRide, $sessions);

        return $form;
    }

    private function getBikeRideClusters(BikeRide $bikeRide): Collection
    {
        /** @var COllection $clusters */
        $clusters = $bikeRide->getClusters();
        if ($clusters->isEmpty()) {
            $this->createClusters->execute($bikeRide);
            $this->entityManager->flush();
            $clusters = $bikeRide->getClusters();
        }

        return $clusters;
    }

    private function getSurveyResponse(BikeRide $bikeRide): ?array
    {
        $surveyResponses = null;
        if (null !== $bikeRide->getSurvey() && !$bikeRide->getSurvey()->getSurveyIssues()->isEmpty()) {
            $uuid = uniqid('', true);
            foreach ($bikeRide->getSurvey()->getSurveyIssues() as $issue) {
                $response = new SurveyResponse();
                $response->setSurveyIssue($issue)
                    ->setUuid($uuid)
                ;
                $surveyResponses[] = $response;
            }
        }

        return $surveyResponses;
    }

    private function getUserSession(BikeRide $bikeRide, User $user, Collection $clusters): Session
    {
        $userSession = $this->sessionRepository->findOneByUserAndClusters($user, $clusters);

        if (null === $userSession) {
            $userCluster = $this->sessionService->getCluster($bikeRide, $user, $clusters);
            $userSession = new Session();
            $userSession->setUser($user)
                ;

            if (null !== $userCluster) {
                $userSession->setCluster($userCluster);
            }
        }

        return $userSession;
    }

    private function getForm(Session $userSession, BikeRide $bikeRide, Collection $clusters, bool $isEndTesting): FormInterface
    {
        return $this->formFactory->create(SessionType::class, [
            'session' => $userSession,
            'responses' => ['surveyResponses' => $this->getSurveyResponse($bikeRide)],
        ], [
            'clusters' => $clusters,
            'is_writable_availability' => $this->isWritableAvailability->execute($bikeRide, $userSession->getUser()),
        ]);
    }

    private function setParams(FormInterface $form, BikeRide $bikeRide, array $sessions): void
    {
        $this->params = [
            'form' => $form->createView(),
            'bikeRide' => $bikeRide,
            'sessions' => $sessions,
        ];
    }
}
