<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Dto\DtoTransformer\LicenceDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Session;
use App\Form\Admin\SessionType;
use App\Form\SessionSwitchType;
use App\Repository\SessionRepository;
use App\Service\CacheService;
use App\Service\LicenceService;
use App\Service\MessageService;
use App\Service\SeasonService;
use App\Service\SessionService;
use App\Service\SurveyService;
use App\UseCase\Session\SetSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SessionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheService $cacheService,
        private readonly SessionService $sessionService,
        private readonly SurveyService $surveyService,
        private readonly SessionRepository $sessionRepository,
        private readonly BikeRideDtoTransformer $bikeRideDtoTransformer,
        private readonly SetSession $setSession,
    ) {
    }

    #[Route('/admin/seance', name: 'admin_session_present', methods: ['POST'], options:['expose' => true])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminPresent(
        Request $request,
        SessionRepository $sessionRepository,
        LicenceService $licenceService,
    ): Response {
        $codeError = 1;
        $sessionId = $request->request->get('sessionId');
        $session = ($sessionId) ? $sessionRepository->find($sessionId) : null;
        if ($session) {
            $cachePool = new FilesystemAdapter();
            $cachePool->deleteItem(sprintf('cluster.%s', $session->getCluster()->getId()));

            $isPresent = !$session->isPresent();
            $session->setIsPresent($isPresent);
            $this->entityManager->flush();

            $licenceService->applyCompleteTrial($session->getUser());

            $this->cacheService->deleteCacheIndex($session->getCluster());
            $codeError = 0;
        }

        return new JsonResponse(['codeError' => $codeError]);
    }

    #[Route('/admin/session/message/{session}', name: 'admin_session_message', methods: ['GET'], options:['expose' => true])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminMessage(
        Session $session,
        UserDtoTransformer $userDtoTransformer,
        MessageService $messageService,
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl('admin_session_present'),
        ]);
        $form->add('sessionId', HiddenType::class, ['data' => $session->getId()]);


        $user = $userDtoTransformer->getHeaderFromEntity($session->getUser());
        $message = '';
        if ($user->mustProvideRegistration) {
            $message = $messageService->getMessageByName('BIKE_RIDE_MUST_PROVIDE_REGISTRATION', $user);
        }
        if ($user->isEndTesting) {
            $message = $messageService->getMessageByName('BIKE_RIDE_END_TESTING', $user);
        }

        return new JsonResponse([
            'codeError' => 0,
            'modal' => $this->renderView('session/admin/message.html.twig', [
                'message' => $message,
                'form' => $form->createView(),
            ]),
        ]);
    }

    #[Route('/admin/groupe/change/{session}', name: 'admin_bike_ride_switch_cluster', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminClusterSwitch(
        Request $request,
        Session $session
    ): Response {
        $bikeRide = $session->getCluster()->getBikeRide();
        $form = $this->createForm(SessionSwitchType::class, $session);
        $oldCluster = $session->getCluster();

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $session = $form->getData();
            $this->entityManager->flush();
            $this->cacheService->deleteCacheIndex($oldCluster);
            $this->cacheService->deleteCacheIndex($session->getCluster());

            return $this->redirectToRoute('admin_bike_ride_cluster_show', [
                'bikeRide' => $bikeRide->getId(),
            ]);
        }

        return $this->render('session/switch.html.twig', [
            'bikeRide' => $bikeRide,
            'session' => $session,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/rando/inscription/{bikeRide}', name: 'admin_session_add', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'bikeRide')]
    public function adminSessionAdd(
        Request $request,
        SeasonService $seasonService,
        BikeRide $bikeRide
    ): Response {
        $clusters = $bikeRide->getClusters();
        $request->getSession()->set('admin_session_add_clusters', serialize($clusters));
        $data = [];
        $currentSeason = $seasonService->getCurrentSeason();
        $minSeasonToTakePart = $seasonService->getMinSeasonToTakePart();
        $data['season'] = ($minSeasonToTakePart < $currentSeason) ? null : sprintf('SEASON_%s', $currentSeason);
        if ($bikeRide->getSurvey()) {
            $data['responses'] = ['surveyResponses' => $this->surveyService->getSurveyResponsesFromBikeRide($bikeRide)];
        }
        $form = $this->createForm(SessionType::class, $data, [
            'filters' => ['bikeRide' => $bikeRide->getId(), 'is_yearly_licence' => false, ],
            'bikeRide' => $bikeRide,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $data['user'];

            if (null === $this->sessionRepository->findOneByUserAndBikeRide($user, $bikeRide)) {
                $this->setSession->addFromdmin($data, $user, $bikeRide);
                
                $this->addFlash('success', 'Le participant a bien été inscrit');

                $this->sessionService->checkEndTesting($user);

                return $this->redirectToRoute('admin_bike_ride_cluster_show', [
                    'bikeRide' => $bikeRide->getId(),
                ]);
            }
            $this->addFlash('danger', 'Le participant est déjà inscrit');
        }

        return $this->render('session/admin/add.html.twig', [
            'form' => $form->createView(),
            'bikeRide' => $this->bikeRideDtoTransformer->fromEntity($bikeRide),
        ]);
    }

    #[Route('/admin/rando/supprime/{session}', name: 'admin_session_delete', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_VIEW', 'session')]
    public function adminSessionDelete(
        UserDtoTransformer $userDtoTransformer,
        Session $session,
    ) {
        $userDto = $userDtoTransformer->fromEntity($session->getUser());
        $bikeRide = $session->getCluster()->getBikeRide();
        $this->setSession->delete($session);

        $this->addFlash('success', $userDto->member->fullName . ' à bien été désinscrit');

        return $this->redirectToRoute('admin_bike_ride_cluster_show', [
            'bikeRide' => $bikeRide->getId(),
        ]);
    }
}
