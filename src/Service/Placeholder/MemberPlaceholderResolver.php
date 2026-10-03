<?php

declare(strict_types=1);

namespace App\Service\Placeholder;

use App\Entity\Member;

readonly class MemberPlaceholderResolver implements PlaceholderResolverInterface
{
    public function supports(?object $entity): bool
    {
        return $entity instanceof Member;
    }

    public function resolve(string $template, ?object $entity = null): string
    {
        if (!$entity instanceof Member) {
            return $template;
        }

        return preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',
            fn (array $matches) => $this->getValueForPlaceholder($matches[1], $entity) ?? $matches[0],
            $template
        ) ?? $template;
    }

    private function getValueForPlaceholder(string $placeholder, Member $member): ?string
    {
        $identity = $member->getIdentity();

        return match ($placeholder) {
            'prenom_nom' => $identity?->getFullName(),
            'numero_licence' => $member->getLicenceNumber(),
            'email_principal' => $member->getMainIdentity()?->getEmail(),
            'date_naissance', 'date_naissance_enfant' => $identity?->getBirthDate()?->format('d/m/Y'),
            'lieu_naissance' => $identity?->getBirthCommune()?->getName(),
            default => null,
        };
    }
}
