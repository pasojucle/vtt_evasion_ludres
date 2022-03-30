<?php

declare(strict_types=1);

namespace App\ViewModel\Content;

use App\ViewModel\ServicesPresenter;
use Doctrine\Common\Collections\Collection;

class ContentsViewModel
{
    public ?array $contents = [];
    public ?array $homeContents = [];


    public static function fromContents(array|Collection $contents, ServicesPresenter $services): ContentsViewModel
    {
        $contentsViewModel = [];
        if (!empty($contents)) {
            foreach ($contents as $content) {
                $contentView = ContentViewModel::fromContent($content, $services);
                $contentsViewModel[] = $contentView;
                $type = ($content->isFlash()) ? 'flashes' : 'contents';
                $homeContentsViewModel[$type][] = $contentView;
            }
        }

        $contentsView = new self();
        $contentsView->contents = $contentsViewModel;
        $contentsView->homeContents = $homeContentsViewModel;

        return $contentsView;
    }
}