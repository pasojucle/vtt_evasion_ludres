<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Member;
use App\Entity\Session;
use App\Form\SessionType;
use App\Repository\SessionRepository;
use App\Service\SessionService;
use App\Service\SurveyService;
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
        private readonly SurveyService $surveyService,
    ) {
    }

    public function toAdd(Member $member, BikeRide $bikeRide): FormInterface
    {
        $clusters = $this->getBikeRideClusters($bikeRide);
        $userSession = $this->getUserSession($bikeRide, $member, $clusters);
        $isWritableAvailability = $this->isWritableAvailability->execute($bikeRide, $userSession->getUser());
        $sessions = ($isWritableAvailability)
            ? $this->sessionService->getSessionsBytype($bikeRide)
            : $this->sessionService->getBikeRideMembers($bikeRide);

        $surveyResponses = ($bikeRide->getSurvey()) ? $this->surveyService->getResponsesByUserAndSurvey($member, $bikeRide->getSurvey()) : null;

        $form = $this->getForm($userSession, $bikeRide, $clusters, $isWritableAvailability, $surveyResponses);

        $this->setParams($form, $bikeRide, $sessions);

        return $form;
    }

    public function toEdit(Session $session): FormInterface
    {
        $bikeRide = $session->getCluster()->getBikeRide();
        $member = $session->getMember();
        $clusters = $this->getBikeRideClusters($bikeRide);
        $isWritableAvailability = true;
        $sessions = $this->sessionService->getSessionsBytype($bikeRide, $member);

        $surveyResponses = ($bikeRide->getSurvey())
            ? $this->surveyService->getResponsesByUserAndSurvey($member, $bikeRide->getSurvey())
            : null;

        $surveyHistories = ($surveyResponses)
            ? $this->surveyService->getHistory($bikeRide->getSurvey(), $member)
            : null;
        $form = $this->getForm($session, $bikeRide, $clusters, $isWritableAvailability, $surveyResponses);

        $this->setParams($form, $bikeRide, $sessions, $surveyHistories);

        return $form;
    }

    private function getBikeRideClusters(BikeRide $bikeRide): Collection
    {
        /** @var Collection $clusters */
        $clusters = $bikeRide->getClusters();
        if ($clusters->isEmpty()) {
            $this->createClusters->execute($bikeRide);
            $this->entityManager->flush();
            $clusters = $bikeRide->getClusters();
        }

        return $clusters;
    }

    private function getUserSession(BikeRide $bikeRide, Member $member, Collection $clusters): Session
    {
        $userSession = $this->sessionRepository->findOneByUserAndClusters($member, $clusters);

        if (null === $userSession) {
            $userCluster = $this->sessionService->getCluster($bikeRide, $member, $clusters);
            $userSession = new Session();
            $userSession->setUser($member);

            if (null !== $userCluster) {
                $userSession->setCluster($userCluster);
            }
        }

        return $userSession;
    }

    private function getForm(Session $userSession, BikeRide $bikeRide, Collection $clusters, bool $isWritableAvailability, ?array $surveyResponses): FormInterface
    {
        /** @var BikeRideType $bikeRideType */
        $bikeRideType = $bikeRide->getBikeRideType();

        return $this->formFactory->create(SessionType::class, [
            'session' => $userSession,
            'responses' => ['surveyResponses' => $surveyResponses],
        ], [
            'clusters' => $clusters,
            'is_writable_availability' => $isWritableAvailability,
            'display_bike_kind' => $bikeRideType->isDisplayBikeKind(),
        ]);
    }

    private function setParams(FormInterface $form, BikeRide $bikeRide, array $sessions, ?array $surveyHistories = null): void
    {
        $this->params = [
            'form' => $form->createView(),
            'bikeRide' => $this->bikeRideDtoTransformer->getHeaderFromEntity($bikeRide, $surveyHistories),
            'sessions' => $sessions,
        ];
    }
}
