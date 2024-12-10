<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\NotificationDtoTransformer;
use App\Entity\Survey;
use App\Entity\User;
use App\Service\NotificationService;
use App\Service\UserService;
use App\UseCase\Notification\GetList;
use App\UseCase\Notification\GetNews;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/notification', name: 'notification_')]
class NotificationController extends AbstractController
{
    public function __construct(
        private readonly GetNews $getNews,
        private readonly UserService $userService,
    ) {
    }

    #[Route('s', name: 'list', methods: ['GET'])]
    public function list(GetList $showNotification): JsonResponse
    {
        list($modalNotification, $notifications) = $showNotification->execute();

        return new JsonResponse([
            'modal' => ($modalNotification) ? $this->renderView('notification/show.modal.html.twig', [
                'modal' => $modalNotification,
            ]) : null,
            'notifications' => [
                'total' => count($notifications),
                'list' => $this->renderView('notification/list.html.twig', [
                    'notifications' => $notifications,
                ])
            ]
        ]);
    }

    #[Route('/slideshow', name: 'slideshow', methods: ['GET'], options:['expose' => true])]
    public function slideshow(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $slideshowImages = ($this->userService->licenceIsActive($user)) ? $this->getNews->getSlideShowImages() : null;
        dump($slideshowImages);
        return new JsonResponse(['hasNewItem' => !empty($slideshowImages)]);
    }

    #[Route('/summary/list', name: 'summary_list', methods: ['GET'], options:['expose' => true])]
    public function summaryList(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $summaries = ($this->userService->licenceIsActive($user)) ? $this->getNews->getSummaries() : null;

        return new JsonResponse(['hasNewItem' => !empty($summaries)]);
    }

    #[Route('/notification/secondHand', name: 'second_hand', methods: ['GET'], options:['expose' => true])]
    public function secondHand(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $secondHands = ($this->userService->licenceIsActive($user)) ? $this->getNews->getSecondHands() : null;

        return new JsonResponse(['hasNewItem' => !empty($secondHands)]);
    }

    #[Route('/notification/show/{entityName}/{entityId}', name: 'show', methods: ['GET'], defaults:['entityId' => null])]
    public function show(
        EntityManagerInterface $entityManager,
        NotificationService $notificationService,
        NotificationDtoTransformer $notificationDtoTransformer,
        string $entityName,
        ?int $entityId
    ): Response {
        try {
            $entity = new ReflectionClass(sprintf('App\Entity\%s', $entityName));
        } catch (ReflectionException) {
            $entity = $entityName;
        }

        $notification = match ($entity) {
            'NEW_SEASON_RE_REGISTRATION_ENABLED' => $notificationService->getNewSeasonReRegistration(),
            'SURVEY_CHANGED' => $notificationService->getSurveyChanged($entityManager->getRepository(Survey::class)->find($entityId)),
            default => $entityManager->getRepository($entity->getName())->find($entityId)
        };

        return $this->render('notification/show.modal.html.twig', [
            'modal' => $notificationDtoTransformer->fromEntity($notification),
        ]);
    }
}
