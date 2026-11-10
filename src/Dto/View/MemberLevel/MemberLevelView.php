<?php

declare(strict_types=1);

namespace App\Dto\View\MemberLevel;

use App\Core\Contract\View\ComponentViewInterface;
use App\Core\Contract\View\TurboStreamViewInterface;
use App\Dto\View\BadgeView;
use App\Dto\View\LinkView;

readonly class MemberLevelView implements TurboStreamViewInterface, ComponentViewInterface
{
    public function __construct(
        public int $id,
        public BadgeView $level,
        public ?string $levelType,
        public LinkView $action,
    ) {
    }

    public function getStreamTemplate(): string
    {
        return 'member_level/admin/update.lazy.html.twig';
    }
    
    public function getName(): string
    {
        return 'view';
    }

    public function getTemplate(): string
    {
        return 'member_level/admin/show.html.twig';
    }
}
