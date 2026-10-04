<?php

declare(strict_types=1);

namespace App\Service\Placeholder;

use App\Entity\Enum\BikeTypeEnum;
use App\Entity\Licence;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class LicencePlaceholderResolver extends AbstractPlaceholderResolver
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function supports(?object $data): bool
    {
        return $data instanceof Licence;
    }

    protected function getValueForPlaceholder(string $placeholder, ?object $data): ?string
    {
        /** @var Licence $data */
        return match ($placeholder) {
            'saison' => $data->getSeason(),
            'full_saison' => $data->getFullSeason(),
            'date' => ($data->getState()->isYearly())
                ? $data->getCreatedAt()->format('d/m/Y')
                : $data->getTestingAt()?->format('d/m/Y'),
            'type_assurance' => $data->getCoverage()->trans($this->translator),
            'VTTAE' => (BikeTypeEnum::ELECTRIC === $data->getBikeType()) ? 'Oui' : 'Non',
            'montant', 'cotisation' => $data->getAmount()?->__toString(),
            default => null,
        };
    }
}
