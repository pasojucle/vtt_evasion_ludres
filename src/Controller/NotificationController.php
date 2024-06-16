<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\SlideshowService;
use App\UseCase\Notification\GetList;
use App\UseCase\Notification\GetNews;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/notification', name: 'notification_')]
class NotificationController extends AbstractController
{
    public function __construct(
        private readonly GetNews $getNews,
    ) {
    }

    #[Route('s', name: 'list', methods: ['GET'])]
    public function list(GetList $showNotification): Response
    {
        $modal = $showNotification->execute();

        if (null !== $modal) {
            return $this->render('notification/show.modal.html.twig', [
                'modal' => $modal,
            ]);
        }

        return new Response('', 204);
    }

    #[Route('/slideshow', name: 'slideshow', methods: ['GET'], options:['expose' => true])]
    public function slideshow(): JsonResponse
    {
        $slideshowImages = $this->getNews->getSlideShowImages();

        return new JsonResponse(['hasNewItem' => !empty($slideshowImages)]);
    }

    #[Route('/summary/list', name: 'summary_list', methods: ['GET'], options:['expose' => true])]
    public function summaryList(): Response
    {
        $summaries = $this->getNews->getSummaries();

        return new JsonResponse(['hasNewItem' => !empty($summaries)]);
    }

    #[Route('/notification/secondHand', name: 'second_hand', methods: ['GET'], options:['expose' => true])]
    public function secondHand(): Response
    {
        $secondHands = $this->getNews->getSecondHands();

        return new JsonResponse(['hasNewItem' => !empty($secondHands)]);
    }
}
