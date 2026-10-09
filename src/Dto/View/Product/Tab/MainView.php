<?php

declare(strict_types=1);

namespace App\Dto\View\Product\Tab;

use App\Core\Contract\View\TabContentInterface;

readonly class MainView implements TabContentInterface
{
    public function getName(): string
    {
        return 'content';
    }

    public function getTemplate(): string
    {
        return 'product/admin/edit/tab_general.html.twig';
    }
}
