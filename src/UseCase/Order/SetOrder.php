<?php

declare(strict_types=1);

namespace App\UseCase\Order;

use App\Entity\OrderHeader;
use Symfony\Component\Form\Form;
use App\Entity\Enum\OrderStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\ClickableInterface;

class SetOrder 
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
        
    }

    public function execute(Form $form, OrderHeader $orderHeader): array
    {
        $route = 'admin_orders';
        $params = ['filtered' => true];
        /** @var ?SubmitButton  $validate */
        $validate = ($form->has('validate') && $form->get('validate') instanceof ClickableInterface) ? $form->get('validate') : null;
        /** @var ?SubmitButton  $complete */
        $complete = ($form->has('complete') && $form->get('complete') instanceof ClickableInterface) ? $form->get('complete') : null;
        /** @var ?SubmitButton  $unValide */
        $unValide = ($form->has('unValide') && $form->get('unValide') instanceof ClickableInterface) ? $form->get('unValide') : null;
        /** @var ?SubmitButton  $cancel */
        $cancel = ($form->has('cancel') && $form->get('cancel') instanceof ClickableInterface) ? $form->get('cancel') : null;
        dump($cancel);
        if ($validate && $validate->isClicked()) {
            $orderHeader->setStatus(OrderStatusEnum::VALIDED);
        }

        if ($complete && $complete->isClicked()) {
            $orderHeader->setStatus(OrderStatusEnum::COMPLETED);
        }

        if ($cancel && $cancel->isClicked()) {
            $orderHeader->setStatus(OrderStatusEnum::CANCELED);
        }

        if ($unValide && $unValide->isClicked()) {
            $orderHeader->setStatus(OrderStatusEnum::ORDERED);
            $route = 'admin_order';
            $params = ['orderHeader' => $orderHeader->getId()];
        }
    
        $this->entityManager->flush();

        return [$route, $params];
    }
}