<?php

declare(strict_types=1);

namespace App\Service\Placeholder;

use App\Entity\MemberGardian;

readonly class GardianPlaceholderResolver extends AbstractPlaceholderResolver
{
    public function supports(?object $data): bool
    {
        return $data instanceof MemberGardian;
    }

    protected function getValueForPlaceholder(string $placeholder, ?object $data): ?string
    {
        /** @var MemberGardian $data */
        $identity = $data->getIdentity();
        $gardianAddress = $identity?->getAddress();
        $address = $gardianAddress
            ? $gardianAddress->__tostring()
            : $data->getMember()->getIdentity()->getAddress()->__tostring();

        return match ($placeholder) {
            'prenom_nom_parent' => $identity?->getFullName(),
            'adresse_parent' => $address,
            default => null,
        };
    }
}
