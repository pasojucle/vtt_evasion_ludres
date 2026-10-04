<?php

declare(strict_types=1);

namespace App\Service\Placeholder;


readonly class DataPlaceholderResolver extends AbstractPlaceholderResolver
{
    public function supports(?object $data): bool
    {
        return $data instanceof DataPlaceholder;
    }

    protected function getValueForPlaceholder(string $placeholder, ?object $data): ?string
    {
        return $data->values[$placeholder] ?? null;
    }
}
