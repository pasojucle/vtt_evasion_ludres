<?php

declare(strict_types=1);

namespace App\Dto\View\Order;

use App\Core\Contract\View\ComponentFormViewInterface;
use App\Dto\View\EmailView;
use App\Dto\View\PhoneView;

readonly class OrderSheetView implements ComponentFormViewInterface
{
    /**
     * @param OrderLineView[] $orderLines
     */
    public function __construct(
        public string $title, 
        public string $description, 
        public string $action, 
        public string $frame,
        public string $fullName,
        public PhoneView $phone,
        public EmailView $email,
        public array $orderLines,
        public string $amount,
        public ?string $comments,
    ){}

    public function getName(): string
    {
        return 'view';
    }

    public function getTemplate(): string
    {
        return 'order/admin/_form.sheet.html.twig';
    }

    public function getFormAttr(): array
    {
        return [];
    }
}
