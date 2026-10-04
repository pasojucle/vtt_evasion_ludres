<?php

declare(strict_types=1);

namespace App\Service\Placeholder;

abstract readonly class AbstractPlaceholderResolver implements PlaceholderResolverInterface
{
    private const PATTERN = '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/';

    public function resolve(string $template, ?object $data = null): string
    {
        if (!$this->supports($data)) {
            return $template;
        }

        return preg_replace_callback(
            self::PATTERN,
            fn (array $matches) => $this->getValueForPlaceholder($matches[1], $data) ?? $matches[0],
            $template
        ) ?? $template;
    }

    abstract protected function getValueForPlaceholder(string $placeholder, ?object $data): ?string;
}