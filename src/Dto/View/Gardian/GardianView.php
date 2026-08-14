<?php

declare(strict_types=1);

namespace App\Dto\View\Gardian;

use App\Dto\View\EmailView;
use App\Dto\View\Interface\TurboStreamViewInterface;
use App\Dto\View\LinkView;
use App\Dto\View\PhoneView;

readonly class GardianView implements TurboStreamViewInterface
{
    /**
     * @param PhoneView[] $phones
     */
    public function __construct(
        public int $id,
        public string $kind,
        public string $fullName,
        public ?string $address,
        public ?string $city,
        public EmailView $email,
        public array $phones,
        public LinkView $action,
    ) {
    }
}
