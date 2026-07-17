<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Dto\State\HtmlProcessorResult;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @template T of object
 */
interface HtmlProcessorInterface
{
    /**
     * @param T $entity
     * @param UploadedFile[] $uploadFiles
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): HtmlProcessorResult;
}
