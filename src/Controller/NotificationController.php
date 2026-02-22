<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\NotificationDtoTransformer;
use App\Entity\Documentation;
use App\Entity\Survey;
use App\Service\NotificationService;
use App\UseCase\Notification\GetList;
use App\UseCase\Notification\GetNews;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notification', name: 'notification_')]
class NotificationController extends AbstractController
{
    public function __construct(
        private readonly GetNews $getNews,
        private readonly NotificationService $notificationService,
        private readonly NotificationDtoTransformer $notificationDtoTransformer,
    ) {
    }

    #[Route('s', name: 'list', methods: ['GET'])]
    public function list(GetList $showNotification): JsonResponse
    {
        list($modalNotification, $notifications, $repeat) = $showNotification->execute();
        return new JsonResponse([
            'modal' => ($modalNotification)
                ? $this->renderView('notification/show.modal.html.twig', [
                    'data' => $modalNotification,
                ])
                : null,
            'notifications' => [
                'total' => count($notifications),
                'list' => $this->renderView('notification/list.html.twig', [
                    'notifications' => $notifications,
                ])
            ],
            'repeat' => $repeat,
        ]);
    }

    #[Route('/slideshow', name: 'slideshow', methods: ['GET'], options:['expose' => true])]
    public function slideshow(): Response
    {
        $slideshowImages = $this->getNews->getSlideShowImages();

        return $this->render('notification/_frame_novelty.html.twig', [
            'hasNewItem' => !empty($slideshowImages),
            'route' => 'notification_slideshow',
        ]);
    }

    #[Route('/summary/list', name: 'summary_list', methods: ['GET'], options:['expose' => true])]
    public function summaryList(): Response
    {
        $summaries = $this->getNews->getSummaries();

        return $this->render('notification/_frame_novelty.html.twig', [
            'hasNewItem' => !empty($summaries),
            'route' => 'notification_summary_list',
        ]);
    }

    #[Route('/club/menu', name: 'club_menu', methods: ['GET'], options:['expose' => true])]
    public function clubMenu(): Response
    {
        $summaries = $this->getNews->getSummaries();
        $slideshowImages = $this->getNews->getSlideShowImages();

        return $this->render('notification/_frame_novelty.html.twig', [
            'hasNewItem' => !empty($summaries) && !empty($slideshowImages),
            'route' => 'notification_club_menu',
        ]);
    }



    #[Route('/user/skill/list', name: 'user_skill_list', methods: ['GET'], options:['expose' => true])]
    public function userSkillList(): Response
    {
        $userSkills = $this->getNews->getUserSkill();

        return $this->render('notification/_frame_novelty.html.twig', [
            'hasNewItem' => !empty($userSkills),
            'route' => 'notification_user_skill_list',
        ]);
    }

    #[Route('/secondHand', name: 'second_hand', methods: ['GET'], options:['expose' => true])]
    public function secondHand(): Response
    {
        $secondHands = $this->getNews->getSecondHands();

        return $this->render('notification/_frame_novelty.html.twig', [
            'hasNewItem' => !empty($secondHands),
            'route' => 'notification_second_hand',
        ]);
    }

    #[Route('/link', name: 'link', methods: ['GET'], options:['expose' => true])]
    public function link(): Response
    {
        $links = $this->getNews->getLinks();

        return $this->render('notification/_frame_novelty.html.twig', [
            'hasNewItem' => !empty($links),
            'route' => 'notification_link',
        ]);
    }

    #[Route('/documentation', name: 'documentation', methods: ['GET'], options:['expose' => true])]
    public function documentation(): Response
    {
        $documentations = $this->getNews->getDocumentatons();

        return $this->render('notification/_frame_novelty.html.twig', [
            'hasNewItem' => !empty($documentations),
            'route' => 'notification_documentation',
        ]);
    }

    #[Route('/show/{entityName}/{entityId}', name: 'show', methods: ['GET'], defaults:['entityId' => null])]
    public function show(
        EntityManagerInterface $entityManager,
        string $entityName,
        ?int $entityId
    ): Response {
        try {
            $entity = new ReflectionClass(sprintf('App\Entity\%s', $entityName));
        } catch (ReflectionException) {
            $entity = $entityName;
        }

        $notification = match ($entity) {
            'NEW_SEASON_RE_REGISTRATION_ENABLED' => $this->notificationService->getNewSeasonReRegistration(),
            'SURVEY_CHANGED' => $this->notificationService->getSurveyChanged($entityManager->getRepository(Survey::class)->find($entityId)),
            default => $entityManager->getRepository($entity->getName())->find($entityId)
        };

        return $this->render('notification/show.modal.html.twig', [
            'data' => $this->notificationDtoTransformer->fromEntity($notification),
        ]);
    }

    #[Route('/outside/link/{documentation}', name: 'outside_link', methods: ['GET', 'POST'])]
    public function outsideLink(Documentation $documentation): Response
    {
        return $this->render('notification/show.modal.html.twig', [
            'data' => $this->notificationDtoTransformer->fromEntity($this->notificationService->getDocumentation($documentation)),
        ]);
    }
}
