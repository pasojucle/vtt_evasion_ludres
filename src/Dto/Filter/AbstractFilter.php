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
                $properties[$name] = match (true) {
                    $value instanceof BackedEnum => $value->value,
                    is_object($value) && method_exists($value, 'getId') => $value->getId(),
                    default => $value
                };
            }
        }

        return $properties;
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
}
