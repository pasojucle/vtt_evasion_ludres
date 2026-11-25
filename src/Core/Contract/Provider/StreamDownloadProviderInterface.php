<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Core\Dto\HandlerContext;

/**
 * @template T of object
 */
interface StreamDownloadProviderInterface
{
    public function streamDownloadContent(?object $data, ?HandlerContext $context = null): void;

    public function basename(?object $data, ?HandlerContext $context = null): string;

    public function contentType(): string;
}
