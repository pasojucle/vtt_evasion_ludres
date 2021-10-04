<?php

namespace App\ViewModel;

use App\Entity\User;
use App\Entity\OrderHeader;
use App\ViewModel\ProductViewModel;
use Doctrine\Common\Collections\Collection;

class OrderViewModel extends AbstractViewModel
{
    public ?int $id;
    private ?UserViewModel $user;
    private ?Collection $oderLines;
    public ?int $status;
    public ?string $amount;

    public static function fromOrderHeader(OrderHeader $orderHeader)
    {
        $orderView = new self();
        $orderView->id = $orderHeader->getId();
        $orderView->user = UserViewModel::fromUser($orderHeader->getUser());
        $orderView->status = $orderHeader->getStatus();
        $orderView->orderLines = $orderHeader->getOrderLines();

        return $orderView;
    }

    public function getLines(): array
    {
        $lines = [];
        if (!$this->orderLines->isEmpty()) {
            foreach($this->orderLines as $line) {
                $lines[] = [
                    'product' => ProductViewModel::fromProduct($line->getProduct(), ''),
                    'quantity' => $line->getQuantity(),
                    'size' => $line->getSize()->getName(),
                    'amount' => number_format($line->getQuantity() * $line->getProduct()->getPrice(), 2).' €',
                ];
            }
        }

        return $lines;
    }

    public function getAmount(): string
    {
        $amount = 0;
        if (!$this->orderLines->isEmpty()) {
            foreach($this->orderLines as $line) {
                $amount += ($line->getQuantity() * $line->getProduct()->getPrice());
            }
        }

        return number_format($amount, 2).' €';
    }

    public function getMemberFullName(): string
    {
        return $this->user->getMember['fullName'];
    }
}