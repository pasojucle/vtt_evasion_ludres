<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Entity\BikeRide;
use App\Entity\Session;
use App\Entity\SurveyResponse;
use App\Entity\User;
use App\Form\SessionType;
use App\Repository\SessionRepository;
use App\Service\BikeRideService;
use App\Service\MailerService;
use App\Service\SessionService;
use App\ViewModel\BikeRidePresenter;
use App\ViewModel\BikeRideViewModel;
use App\ViewModel\UserPresenter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Security;

class GetFormSession
{
    public array $params;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerService $mailerService,
        private Security $security,
        private BikeRideService $bikeRideService,
        private SessionRepository $sessionRepository,
        private BikeRidePresenter $bikeRidePresenter,
        private FormFactoryInterface $formFactory,
        private SessionService $sessionService,
        private UserPresenter $userPresenter
    ) {
    }

    public function execute(User $user, BikeRide $bikeRide): FormInterface
    {
        $clusters = $this->getBikeRideClusters($bikeRide);
        list($isAlreadyRegistered, $userSession) = $this->getUserSession($bikeRide, $user, $clusters);

        $this->userPresenter->present($user);
        $this->bikeRidePresenter->present($bikeRide);
        list($framers, $members) = $this->sessionService->getSessionsBytype($bikeRide);

        $isEndTesting = $this->userPresenter->viewModel()->isEndTesting();

        $form = $this->getForm($userSession, $this->bikeRidePresenter->viewModel(), $clusters, $isAlreadyRegistered, $isEndTesting);

        $this->setParams($form, $bikeRide, $framers, $members, $isAlreadyRegistered, $isEndTesting);

        return $form;
    }

    private function getBikeRideClusters(BikeRide $bikeRide): Collection
    {
        /** @var COllection $clusters */
        $clusters = $bikeRide->getClusters();
        if ($clusters->isEmpty()) {
            $this->bikeRideService->createClusters($bikeRide);
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

    private function getUserSession(BikeRide $bikeRide, User $user, Collection $clusters): array
    {
        $userSession = $this->sessionRepository->findByUserAndClusters($user, $clusters);
        $isAlreadyRegistered = ($userSession) ? true : false;

        if (null === $userSession) {
            $userCluster = $this->sessionService->getCluster($bikeRide, $user, $clusters);

            $userSession = new Session();
            $userSession->setUser($user)
                ->setCluster($userCluster)
            ;
        }

        return [$isAlreadyRegistered, $userSession];
    }

    private function getForm(Session $userSession, BikeRideViewModel $bikeRide, Collection $clusters, bool $isAlreadyRegistered, bool $isEndTesting): FormInterface
    {
        return $this->formFactory->create(SessionType::class, [
            'session' => $userSession,
            'responses' => ['surveyResponses' => $this->getSurveyResponse($bikeRide->entity)],
        ], [
            'clusters' => $clusters,
            'bikeRide' => $bikeRide,
            'is_already_registered' => $isAlreadyRegistered,
            'is_end_testing' => $isEndTesting,
        ]);
    }

    private function setParams(FormInterface $form, BikeRide $bikeRide, array $framers, array $members, bool $isAlreadyRegistered, bool $isEndTesting): void
    {
        $this->params = [
            'form' => $form->createView(),
            'bikeRide' => $bikeRide,
            'framers' => $framers,
            'members' => $members,
            'is_already_registered' => $isAlreadyRegistered,
            'is_end_testing' => $isEndTesting,
        ];
    }
}
