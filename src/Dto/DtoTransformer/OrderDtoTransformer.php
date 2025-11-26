<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\OrderDto;
use App\Dto\OrderLineDto;
use App\Entity\Enum\OrderLineStateEnum;
use App\Entity\OrderHeader;
use App\Entity\OrderLine;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderDtoTransformer
{
    public function __construct(
        private UserDtoTransformer $userDtoTransformer,
        private OrderLineDtoTransformer $orderLineDtoTransformer,
        private TranslatorInterface $translator
    ) {
    }

    public function fromEntity(?OrderHeader $orderHeader, ?FormInterface $form = null): OrderDto
    {
        $orderDto = new OrderDto();
        if ($orderHeader) {
            $orderDto->id = $orderHeader->getId();
            $createdAt = $orderHeader->getCreatedAt();
            $orderDto->createdAt = $createdAt->format('d/m/Y');
            $orderDto->user = $this->userDtoTransformer->fromEntity($orderHeader->getUser());
            $orderDto->status = $orderHeader->getStatus();
            $orderDto->statusToString = $this->translator->trans(sprintf('order.status.%s', $orderHeader->getStatus()->value));
            $orderDto->orderLines = $this->orderLineDtoTransformer->fromEntities($orderHeader->getOrderLines(), $orderDto->user, $form?->all()['orderLines']);
            $orderDto->amount = $this->getAmount($orderDto->orderLines);
            $orderDto->comments = $orderHeader->getComments();
        }

        return $orderDto;
    }


    public function fromEntities(Paginator|Collection|array $orderHeaderEntities): array
    {
        $orderHeaders = [];
        foreach ($orderHeaderEntities as $orderHeaderEntity) {
            $orderHeaders[] = $this->fromEntity($orderHeaderEntity);
        }

        return $orderHeaders;
    }

    public function getAmount(array $orderLines): string
    {
        $amount = 0;
        /** @var OrderLineDto $line */
        foreach ($orderLines as $line) {
            if (OrderLineStateEnum::UNAVAILABLE !== $line->state) {
                $amount += $line->amount;
            }
        }

        return number_format($amount, 2) . ' €';
    }
}
