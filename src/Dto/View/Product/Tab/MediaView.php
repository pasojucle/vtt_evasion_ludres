<?php

declare(strict_types=1);

namespace App\Dto\View\Product\Tab;

use App\Core\Contract\View\TabContentInterface;

readonly class MediaView implements TabContentInterface
{
    public function __construct(
        public string $alt,
        public ?string $filename,
        public ?string $filePath,
    ) {
    }
    public function getName(): string
    {
        return 'content';
    }

    public function getTemplate(): string
    {
        return 'product/admin/edit/tab_media.html.twig';
    }
}
