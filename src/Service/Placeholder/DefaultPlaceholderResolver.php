<?php

declare(strict_types=1);

namespace App\Service\Placeholder;

use App\Service\SeasonService;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class DefaultPlaceholderResolver extends AbstractPlaceholderResolver
{
    public function __construct(
        private SeasonService $seasonService,
        private RequestStack $requestStack,
    ) {
    }

    public function supports(?object $data): bool
    {
        return $data === null;
    }

    protected function getValueForPlaceholder(string $placeholder, ?object $data): ?string
    {
        return match ($placeholder) {
            'saison_actuelle' => (string) $this->seasonService->getCurrentSeason(),
            'nom_domaine' => $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost(),
            default => null,
        };
    }
}
