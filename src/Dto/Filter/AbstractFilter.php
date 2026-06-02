<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use BackedEnum;

abstract class AbstractFilter
{
    public function toArray(): array
    {
        $properties = [];
        foreach (get_object_vars($this) as $name => $value) {
            if (null !== $value && '' !== $value) {
                $properties[$name] = $this->normalizeValue($value);
            }
        }

        return $properties;
    }
    public function AllowedtoArray(array $names): array
    {
        $properties = [];
        foreach (get_object_vars($this) as $name => $value) {
            if (in_array($name, $names) && null !== $value && '' !== $value) {
                $properties[$name] = $this->normalizeValue($value);
            }
        }

        return $properties;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->normalizeValue($item), $value);
        }

        return match (true) {
            $value instanceof BackedEnum => $value->value,
            is_object($value) && method_exists($value, 'getId') => $value->getId(),
            default => $value
        };
    }

    public function toQueryParams(?int $page = null): array
    {
        $params = $this->toArray();

        if ($page && $page > 1) {
            $params['p'] = $page;
        }

        return $params;
    }

    public function toEncodedString(?int $page = null): ?string
    {
        $params = $this->toQueryParams($page);
        
        if (empty($params)) {
            return null;
        }

        return base64_encode(http_build_query($params));
    }

    public function getUnsetValue(string $name): mixed
    {
        return null;
    }
}
