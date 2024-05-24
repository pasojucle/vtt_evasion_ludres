<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Session;
use App\Entity\SurveyResponse;
use App\Entity\User;
use App\Form\SessionType;
use App\Repository\SessionRepository;
use App\Service\SessionService;
use App\UseCase\BikeRide\CreateClusters;
use App\UseCase\BikeRide\IsWritableAvailability;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class GetFormSession
{
    public array $params;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CreateClusters $createClusters,
        private readonly SessionRepository $sessionRepository,
        private readonly FormFactoryInterface $formFactory,
        private readonly SessionService $sessionService,
        private readonly IsWritableAvailability $isWritableAvailability,
        private readonly BikeRideDtoTransformer $bikeRideDtoTransformer,
    ) {
    }

    public function execute(User $user, BikeRide $bikeRide): FormInterface
    {
        $clusters = $this->getBikeRideClusters($bikeRide);
        $userSession = $this->getUserSession($bikeRide, $user, $clusters);
        $isWritableAvailability = $this->isWritableAvailability->execute($bikeRide, $userSession->getUser());
        $sessions = ($isWritableAvailability)
            ? $this->sessionService->getSessionsBytype($bikeRide)
            : $this->sessionService->getBikeRideMembers($bikeRide);

        $form = $this->getForm($userSession, $bikeRide, $clusters, $isWritableAvailability);

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

    private function getForm(Session $userSession, BikeRide $bikeRide, Collection $clusters, bool $isWritableAvailability): FormInterface
    {
        /** @var BikeRideType $bikeRideType */
        $bikeRideType = $bikeRide->getBikeRideType();

        return $this->formFactory->create(SessionType::class, [
            'session' => $userSession,
            'responses' => ['surveyResponses' => $this->getSurveyResponse($bikeRide)],
        ], [
            'clusters' => $clusters,
            'is_writable_availability' => $isWritableAvailability,
            'display_bike_kind' => $bikeRideType->isDisplayBikeKind(),
        ]);
    }

    private function setParams(FormInterface $form, BikeRide $bikeRide, array $sessions): void
    {
        $customChecks = null;

        $this->params = [
            'form' => $form->createView(),
            'custom_checks' => $customChecks,
            'bikeRide' => $this->bikeRideDtoTransformer->getHeaderFromEntity($bikeRide),
            'sessions' => $sessions,
        ];
    }
}
