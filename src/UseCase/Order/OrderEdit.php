<?php

declare(strict_types=1);

namespace App\UseCase\Order;

use DateTime;
use App\Service\MailerService;
use App\Entity\Enum\OrderStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderEdit
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerService $mailerService,
        private UrlGeneratorInterface $urlGenerator
    ) {

    }

    public function execute(FormInterface $form): string | false
    {
        /** @var ?SubmitButton  $save */
        $save = ($form->has('save') && $form->get('save') instanceof ClickableInterface) ? $form->get('save') : null;
        if ($save && $save->isClicked()) {
            return $this->validate($form);
        }

        if ($url = $this->deleteLine($form)) {
            return $url;
        }

        $this->entityManager->flush();

        return false;
    }

    private function validate(FormInterface $form): string
    {
        $orderHeader = $form->getData();

        $orderHeader->setCreatedAt(new DateTime())
            ->setStatus(OrderStatusEnum::ORDERED)
        ;
        $this->entityManager->persist($orderHeader);
        $this->entityManager->flush();

        $identity = $orderHeader->getUser()->getIdentity();
        $this->mailerService->sendMailToClub([
            'name' => $identity->getName(),
            'firstName' => $identity->getFirstName(),
            'email' => $identity->getEmail(),
            'subject' => 'Nouvelle commande passéee sur le site VTT Evasion Ludres',
            'order' => $this->urlGenerator->generate('order_acknowledgement', [
                'orderHeader' => $orderHeader->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->urlGenerator->generate('order', [
            'orderHeader' => $orderHeader->getId(),
        ]);
    }

    private function deleteLine(FormInterface $form): string|false
    {
        foreach ($form->get('orderLines') as $lineForm) {
            if ($lineForm->get('remove')->isClicked()) {
                $orderLine = $lineForm->getData();
                
                $this->entityManager->remove($orderLine);
                $this->entityManager->flush();

                return $this->urlGenerator->generate('order_edit', [], Response::HTTP_SEE_OTHER);
            }
        }
        
        return false;
    }
}
