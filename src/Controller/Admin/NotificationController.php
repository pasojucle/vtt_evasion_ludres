<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Notification;
use App\Form\Admin\NotificationType;
use App\Repository\NotificationRepository;
use App\Repository\ParameterRepository;
use App\Service\MessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/popup', name: 'admin_notification_')]
class NotificationController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('s', name: 'list', methods: ['GET'])]
    #[IsGranted('MODAL_WINDOW_LIST')]
    public function list(
        NotificationRepository $notificationRepository,
    ): Response {
        return $this->render('notification/admin/list.html.twig', [
            'notification_list' => $notificationRepository->findAllDesc(),
        ]);
    }

    #[Route('/', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('MODAL_WINDOW_ADD')]
    public function add(Request $request): Response
    {
        $notification = null;
        $form = $this->createForm(NotificationType::class, null);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $notification = $form->getData();
            $this->entityManager->persist($notification);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_notification_list');
        }

        return $this->render('notification/admin/edit.html.twig', [
            'notification' => $notification,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{notification}', name: 'edit', methods: ['GET', 'POST'], requirements:['notification' => '\d+'])]
    #[IsGranted('MODAL_WINDOW_EDIT', 'notification')]
    public function edit(Request $request, ?Notification $notification): Response
    {
        $form = $this->createForm(NotificationType::class, $notification);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_notification_list');
        }

        return $this->render('notification/admin/edit.html.twig', [
            'notification' => $notification,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/toggle/{notification}', name: 'toggle_disable', methods: ['GET'])]
    #[IsGranted('MODAL_WINDOW_EDIT', 'notification')]
    public function toggle(Notification $notification): Response
    {
        $notification->setIsDisabled(!$notification->isDisabled());
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_notification_list');
    }
}
