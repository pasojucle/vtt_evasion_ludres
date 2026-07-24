<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Dto\State\HtmlProcessorResultInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @template T of object
 */
interface FormProcessorInterface
{
    /**
     * @param T $entity
     * @param UploadedFile[] $uploadFiles
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): HtmlProcessorResultInterface;
}
