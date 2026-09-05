<?php

declare(strict_types=1);

namespace App\Dto\View\User\Tab;

use App\Dto\View\BadgeView;
use App\Dto\View\Interface\TabContentInterface;
use Doctrine\Common\Collections\Collection;

readonly class LicenceView implements TabContentInterface
{
    public function __construct(
        public int $id,
        public int $licenceId,
        public ?int $healthId,
    ) {
    }

    public function getName(): string
    {
        return 'content';
    }

    public function getTemplate(): string
    {
        return 'user/admin/show/tab_licence.html.twig';
    }
}