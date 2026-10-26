<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Dto\Filter\AbstractFilter;

/**
 * @template T of object
 */
interface StreamActionExportableInterface
{
    public function streamExportContent(?object $data, ?AbstractFilter $filter): void;

    public function basename(object $data): string;
}
