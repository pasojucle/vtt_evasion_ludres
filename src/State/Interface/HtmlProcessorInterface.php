<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Dto\State\HtmlProcessorResult;

/**
 * @template T of object
 */
interface HtmlProcessorInterface
{
    /**
     * @param T $entity
     */
    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult;
}
