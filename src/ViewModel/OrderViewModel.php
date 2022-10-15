<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\OrderHeader;
use App\ViewModel\UserViewModel;

class OrderViewModel extends AbstractViewModel
{
    public ?int $id;

    public ?UserViewModel $user;

    public ?OrderLinesViewModel $orderLines;

    public ?int $status;

    public ?string $statusToString = '';

    public ?string $amount;

    public string $createdAt;

    public static function fromOrderHeader(OrderHeader $orderHeader, ServicesPresenter $services)
    {
        $orderView = new self();
        $orderView->id = $orderHeader->getId();
        $createdAt = $orderHeader->getCreatedAt();
        $orderView->createdAt = $createdAt->format('d/m/Y');
        $orderView->user = UserViewModel::fromUser($orderHeader->getUser(), $services);
        $orderView->status = $orderHeader->getStatus();
        $orderView->statusToString = $services->translator->trans(OrderHeader::STATUS[$orderView->status]);
        $orderView->orderLines = OrderLinesViewModel::fromOrderLines($orderHeader->getOrderLines(), $orderView->user, $services);
        $orderView->amount = $orderView->getAmount();

        return $orderView;
    }

    public function getAmount(): string
    {
        $amount = 0;
        if (!empty($this->orderLines->lines)) {
            foreach ($this->orderLines->lines as $line) {
                $amount += $line->amount;
            }
        }

        return number_format($amount, 2) . ' €';
    }

    public function getMemberFullName(): string
    {
        return $this->user->member->fullName;
    }
}
