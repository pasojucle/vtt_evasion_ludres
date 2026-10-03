<?php

declare(strict_types=1);

namespace App\Service\Placeholder;

use App\Entity\Enum\BikeTypeEnum;
use App\Entity\Licence;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class LicencePlaceholderResolver implements PlaceholderResolverInterface
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function supports(?object $entity): bool
    {
        return $entity instanceof Licence;
    }

    public function resolve(string $template, ?object $entity = null): string
    {
        if (!$entity instanceof Licence) {
            return $template;
        }

        return preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',
            fn (array $matches) => $this->getValueForPlaceholder($matches[1], $entity) ?? $matches[0],
            $template
        ) ?? $template;
    }

    private function getValueForPlaceholder(string $placeholder, Licence $licence): ?string
    {
        return match ($placeholder) {
            'saison' => $licence->getSeason(),
            'full_saison' => $licence->getFullSeason(),
            'date' => ($licence->getState()->isYearly())
                ? $licence->getCreatedAt()->format('d/m/Y')
                : $licence->getTestingAt()?->format('d/m/Y'),
            'type_assurance' => $licence->getCoverage()->trans($this->translator),
            'VTTAE' => (BikeTypeEnum::ELECTRIC === $licence->getBikeType()) ? 'Oui' : 'Non',
            'montant', 'cotisation' => $licence->getAmount()?->__toString(),
            default => null,
        };
    }
}
