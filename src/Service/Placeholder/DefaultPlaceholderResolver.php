<?php

declare(strict_types=1);

namespace App\Service\Placeholder;

use App\Service\SeasonService;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class DefaultPlaceholderResolver implements PlaceholderResolverInterface
{
    public function __construct(
        private SeasonService $seasonService,
        private RequestStack $requestStack,
    ) {
    }

    public function supports(?object $entity): bool
    {
        return $entity === null;
    }

    public function resolve(string $template, ?object $entity = null): string
    {
        if (null !== $entity) {
            return $template;
        }

        return preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',
            fn (array $matches) => $this->getValueForPlaceholder($matches[1]) ?? $matches[0],
            $template
        ) ?? $template;
    }

    private function getValueForPlaceholder(string $placeholder): ?string
    {
        return match ($placeholder) {
            'saison_actuelle' => (string) $this->seasonService->getCurrentSeason(),
            'nom_domaine' => $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost(),
            default => null,
        };
    }
}
