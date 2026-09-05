<?php

declare(strict_types=1);

namespace App\Dto\View\User\Tab;

use App\Dto\View\BadgeView;
use App\Dto\View\Interface\TabWrapperHeaderInterface;

readonly class WrapperHeaderView implements TabWrapperHeaderInterface
{
    /**
     * @param BadgeView[] $authorizations
     */
    public function __construct(
        public int $id,
        public string $fullName,
        public ?string $levelType,
        public BadgeView $level,
        public BadgeView $season,
        public array $authorizations,
    ) {
    }

    public function getName(): string
    {
        return 'header';
    }

    public function getTemplate(): string
    {
        return 'user/admin/show/tab_header.html.twig';
    }
}
