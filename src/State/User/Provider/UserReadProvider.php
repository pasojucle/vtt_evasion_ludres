<?php

declare(strict_types=1);

namespace App\State\User\Provider;

use App\Dto\Enum\Size;
use App\Dto\View\LinkView;
use App\Dto\View\TabView;
use App\Dto\View\TabWrapperView;
use App\Entity\Enum\LevelType;
use App\Entity\User;
use App\Mapper\User\UserReadMapper;
use App\Service\SeasonService;
use App\State\Interface\ComponentProviderInterface;

/**
 * @implements ComponentProviderInterface<User>
 */
class UserReadProvider implements ComponentProviderInterface
{
    public function __construct(
        private UserReadMapper $mapper,
        private SeasonService $seasonService,
    ) {
    }

    public function getView(object $entity, ?string $fallback = null): TabWrapperView
    {
        $tabs = [
            new TabView('Identité & Contacts', 'lucide:user', 'user/admin/show/tab_identity.html.twig'),
            new TabView('Licence & statut', 'lucide:id-card', 'user/admin/show/tab_licence.html.twig'),
            new TabView('Participation', 'lucide:chart-line', 'user/admin/show/tab_participation.html.twig'),
        ];
        if (LevelType::SCHOOL === $entity->getLevel()?->getType()) {
            $tabs[] = new TabView('Compétences', 'lucide:graduation-cap', 'user/admin/show/tab_skill.html.twig');
        }

        return new TabWrapperView(
            name: sprintf('user-%s', $entity->getId()),
            header: 'user/admin/show/tab_header.html.twig',
            tabs: $tabs,
            entity: $this->mapper->mapToView(
                $entity,
                $this->seasonService->getCurrentSeasonPeriod()
            ),
            fallback: new LinkView(
                url: $fallback,
                icon: 'lucide:chevron-left',
                size: Size::ICON
            ),
        );
    }
}
