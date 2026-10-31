<?php

declare(strict_types=1);

namespace App\Dto\View\Agreement\Tab;

use App\Core\Contract\View\TabContentInterface;

readonly class MainView implements TabContentInterface
{
    public function getName(): string
    {
        return 'content';
    }

    public function getTemplate(): string
    {
        return 'agreement/admin/edit/tab_main.html.twig';
    }
}
