<?php

declare(strict_types=1);

namespace App\Dto\View\Activity\Tab;

use App\Dto\View\Interface\TabContentInterface;

readonly class MediasView implements TabContentInterface
{
    public function __construct(
        public string $alt,
        public ?string $filename,
        public ?string $filePath,
        public bool $isPublic,
    ){

    }
    public function getName(): string
    {
        return 'content';
    }

    public function getTemplate():string
    {
        return 'activity/admin/edit/tab_medias.html.twig';
    }
}