<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\DtoTransformer\ProductDtoTransformer;
use App\Dto\OrderLineDto;
use App\Dto\UserDto;
use App\Entity\OrderLine;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormInterface;

class OrderLineDtoTransformer
{
    public function __construct(
        private ProductDtoTransformer $productDtoTransformer
    ) {
    }

    public function fromEntity(OrderLine $orderLine, UserDto $orderUser, ?string $formName = null): OrderLineDto
    {
        $orderLineDto = new OrderLineDto();
        $orderLineDto->id = $orderLine->getId();
        $orderLineDto->product = $this->productDtoTransformer->fromEntity($orderLine->getProduct(), $orderUser);
        $orderLineDto->quantity = $orderLine->getQuantity();
        $orderLineDto->size = $orderLine->getSize()->getName();
        $orderLineDto->amount = $orderLineDto->quantity * $orderLineDto->product->sellingPrice;
        $orderLineDto->amountToString = number_format($orderLineDto->amount, 2) . ' €';
        $orderLineDto->formName = $formName;
        $orderLineDto->available = $this->getAvailable($orderLine->isAvailable());

        return $orderLineDto;
    }

    public function fromEntities(array|Collection $orderLineEntities, UserDto $orderUser, ?FormInterface $form = null): array
    {
        $ordeLines = [];
        foreach ($orderLineEntities as $key => $orderLine) {
            $ordeLines[] = $this->fromEntity($orderLine, $orderUser, $this->getFormName($key, $form?->all()));
        }

        return $ordeLines;
    }

    private function getFormName(int $key, ?array $form): ?string
    {
        if (null !== $form) {
            $formLine = $form[$key];
            return sprintf('%s[%s][%s][lineId]', $formLine->getParent()->getParent()->getName(), $formLine->getParent()->getName(), $key);
        }
        return null;
    }

    private function getAvailable(?bool $value): array
    {
        $available = match ($value) {
            true => ['color' => 'text-bg-success', 'text' => 'En stock', 'backgroundColor' => 'background-ligth'],
            false => ['color' => 'text-bg-danger', 'text' => 'En commande', 'backgroundColor' => 'background-disbled'],
            default => ['backgroundColor' => 'background-ligth'],
        };

        $available['value'] = $value;
        
        return $available;
    }
}
