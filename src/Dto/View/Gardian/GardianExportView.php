<?php

declare(strict_types=1);

namespace App\Dto\View\Gardian;

use App\Dto\View\PhoneView;
use App\Entity\MemberGardian;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class GardianExportView
{
    public function __construct(
        public string $kind,
        public string $fullName,
        public ?string $address,
        public ?string $email,
        public string $phone,
    ) {
    }

    public static function fromEntity(MemberGardian $gardian, TranslatorInterface $translator): self
    {
        $identity = $gardian->getIdentity();
        $address = $identity->getAddress();
        if (null === $address) {
            $address = $gardian->getMember()->getIdentity()->getAddress();
        }
        return new self(
            $gardian->getKind()->trans($translator),
            $identity->getFullName(),
            $address->__tostring(),
            $identity->getEmail(),
            $identity->getMobile()
        );
    }
}
