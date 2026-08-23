<?php

declare(strict_types=1);

namespace App\Dto\View\Identity;

use App\Dto\View\EmailView;
use App\Dto\View\Interface\TurboStreamViewInterface;
use App\Dto\View\LinkView;
use App\Dto\View\PhoneView;

readonly class IdentityView implements TurboStreamViewInterface
{
    /**
     * @param PhoneView[] $phones
     */
    public function __construct(
        public int $id,
        public string $fullName,
        public string $birthDate,
        public string $birthPlace,
        public string $address,
        public string $city,
        public EmailView $email,
        public array $phones,
        public string $passportPhoto,
        public ?string $profession,
        public LinkView $action,
    ) {
    }

    public function getStreamTemplate(): string
    {
        return 'identity/admin/update.lazy.html.twig';
    }
}
