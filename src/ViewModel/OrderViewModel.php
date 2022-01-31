<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\OrderHeader;

class OrderViewModel extends AbstractViewModel
{
    public ?int $id;

    public ?UserViewModel $user;

    public ?OrderLinesViewModel $orderLines;

    public ?int $status;

    public ?string $amount;

    public static function fromOrderHeader(OrderHeader $orderHeader, array $services)
    {
        $orderView = new self();
        $orderView->id = $orderHeader->getId();
        $createdAt = $orderHeader->getCreatedAt();
        $orderView->createdAt = $createdAt->format('d/m/Y');
        $orderView->user = UserViewModel::fromUser($orderHeader->getUser(), $services);
        $orderView->status = $orderHeader->getStatus();
        $orderView->orderLines = OrderLinesViewModel::fromOrderLines($orderHeader->getOrderLines(), $orderView->user, $services);
        $orderView->amount = $orderView->getAmount();

        return $orderView;
    }

    public function getAmount(): string
    {
        $amount = 0;
        if (! empty($this->orderLines->lines)) {
            foreach ($this->orderLines->lines as $line) {
                $amount += $line['amount_float'];
            }
        }

        return number_format($amount, 2).' €';
    }

    public function getMemberFullName(): string
    {
        return $this->user->getMember['fullName'];
    }
}
