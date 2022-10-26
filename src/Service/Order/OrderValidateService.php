<?php

declare(strict_types=1);

namespace App\Service\Order;

use App\Entity\OrderHeader;
use App\Service\MailerService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderValidateService
{
    private EntityManagerInterface $entityManager;

    private MailerService $mailerService;

    private UrlGeneratorInterface $router;

    public function __construct(
        EntityManagerInterface $entityManager,
        MailerService $mailerService,
        UrlGeneratorInterface $router
    ) {
        $this->entityManager = $entityManager;
        $this->mailerService = $mailerService;
        $this->router = $router;
    }

    public function execute(FormInterface $form): void
    {
        $orderHeader = $form->getData();

        $orderHeader->setCreatedAt(new DateTime())
            ->setStatus(OrderHeader::STATUS_ORDERED)
        ;
        $this->entityManager->persist($orderHeader);
        $this->entityManager->flush();

        $identity = $orderHeader->getUser()->getFirstIdentity();
        $this->mailerService->sendMailToClub([
            'name' => $identity->getName(),
            'firstName' => $identity->getFirstName(),
            'email' => $identity->getEmail(),
            'subject' => 'Nouvelle commande passéee sur le site VTT Evasion Ludres',
            'order' => $this->router->generate('order_acknowledgement', [
                'orderHeader' => $orderHeader->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }
}
