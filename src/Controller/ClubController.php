<?php

namespace App\Controller;


use App\Service\SubscriptionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClubController extends AbstractController
{
    /**
     * @Route("/club", name="club")
     */
    public function index(): Response
    {
        return $this->render('club/index.html.twig', [
            'controller_name' => 'ClubController',
        ]);
    }

    /**
     * @Route("/club/inscription/{type}/{step}", name="subscription", defaults={"step":1})
     */
    public function subscription(
        Request $request,
        SubscriptionService $subscriptionService,
        string $type,
        ?int $step
    ): Response
    {
        return $this->render('club/subscription.html.twig', [
            'type' => $type,
            'step' => $step,
            'progress' => $subscriptionService->getProgress($type, $step),
        ]);
    }
}
