<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\OrderHeader;
use App\Entity\OrderLine;
use App\Entity\Product;
use App\Entity\Size;
use App\ViewModel\UserViewModel;
use Symfony\Component\Form\Form;

class OrderLineViewModel extends AbstractViewModel
{
    public ?int $id;

    public ?ProductViewModel $product = null;

    public int $quantity = 0;

    public string $size = '';

    public float $amount = 0;

    public string $amountToString = '';

    public ?string $formName;

    public static function fromOrderLine(OrderLine $orderLine, UserViewModel $orderUser, ServicesPresenter $services, ?string $formName = null)
    {
        $orderLineView = new self();
        $orderLineView->id = $orderLine->getId();
        $orderLineView->product = ProductViewModel::fromProduct($orderLine->getProduct(), $services, $orderUser);
        $orderLineView->quantity = $orderLine->getQuantity();
        $orderLineView->size = $orderLine->getSize()->getName();
        $orderLineView->amount = $orderLineView->quantity * $orderLineView->product->sellingPrice;
        $orderLineView->amountToString = number_format($orderLineView->amount, 2) . ' €';
        $orderLineView->formName = $formName;

        return $orderLineView;
    }
}
