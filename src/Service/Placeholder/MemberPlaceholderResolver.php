<?php

declare(strict_types=1);

namespace App\Service\Placeholder;

use App\Entity\Member;

readonly class MemberPlaceholderResolver extends AbstractPlaceholderResolver
{
    public function supports(?object $data): bool
    {
        return $data instanceof Member;
    }

    protected function getValueForPlaceholder(string $placeholder, ?object $data): ?string
    {
        /** @var Member $data */
        $identity = $data->getIdentity();

        return match ($placeholder) {
            'prenom_nom', 'prenom_nom_enfant' => $identity?->getFullName(),
            'numero_licence' => $data->getLicenceNumber(),
            'email_principal' => $data->getMainIdentity()?->getEmail(),
            'date_naissance', 'date_naissance_enfant' => $identity?->getBirthDate()?->format('d/m/Y'),
            'lieu_naissance' => $identity?->getBirthCommune()?->getName(),
            default => null,
        };
    }
}
