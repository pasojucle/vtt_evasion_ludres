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
            if ($value) {
                $properties[$name] = $value instanceof BackedEnum
                    ? $value->value
                    : $value;
                }
        }
        return $properties;
    }

    public function toQueryParams(?int $page = null): array
    {
        $params = [];
        foreach (get_object_vars($this) as $name => $value) {
            if ($value !== null) {
                $params[$name] = ($value instanceof BackedEnum) ? $value->value : $value;
            }
        }

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