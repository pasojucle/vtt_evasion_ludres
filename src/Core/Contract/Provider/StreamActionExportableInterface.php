<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

/**
 * @template T of object
 */
interface StreamActionExportableInterface
{
    public function streamExportContent(object $data): void;

    public function basename(object $data): string;
}
