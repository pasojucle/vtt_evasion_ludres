<?php

declare(strict_types=1);

namespace App\State\User\Provider;

use App\Dto\Enum\Size;
use App\Dto\View\LinkView;
use App\Dto\View\TabView;
use App\Dto\View\TabWrapperView;
use App\Entity\Enum\LevelType;
use App\Entity\User;
use App\Mapper\User\Read\IdentityMapper;
use App\Mapper\User\Read\LicenceMapper;
use App\Mapper\User\Read\ParticipationMapper;
use App\Mapper\User\Read\SkillsMapper;
use App\Mapper\User\Read\TabHeaderMapper;
use App\Service\SeasonService;
use App\State\Interface\ComponentProviderInterface;

/**
 * @implements ComponentProviderInterface<User>
 */
class UserReadProvider implements ComponentProviderInterface
{
    public function __construct(
        private TabHeaderMapper $tabHeaderMapper,
        private IdentityMapper $identityMapper,
        private LicenceMapper $licenceMapper,
        private ParticipationMapper $participationMapper,
        private SkillsMapper $skillsMapper,
        private SeasonService $seasonService,
    ) {
    }

    public function getView(object $entity, ?string $fallback = null, ?string $referer = null): TabWrapperView
    {
        $tabs = [
            new TabView(
                title: 'Identité & Contacts', 
                icon: 'lucide:user',
                view: $this->identityMapper->mapToView($entity),
            ),
            new TabView(
                title: 'Licence & statut', 
                icon: 'lucide:id-card', 
                view: $this->licenceMapper->mapToView($entity)
            ),
            new TabView(
                title: 'Participation', 
                icon: 'lucide:chart-line', 
                view: $this->participationMapper->mapToView(
                    $entity,
                    $this->seasonService->getCurrentSeasonPeriod()
                ),
            ),
        ];
        if ($entity->isSchoolMember()) {
            $tabs[] = new TabView(
                title: 'Compétences', 
                icon: 'lucide:badge-check', 
                view: $this->skillsMapper->mapToView($entity),
            );
        }

        return new TabWrapperView(
            name: sprintf('user-%s', $entity->getId()),
            title: $entity->getIdentity()->getFullName(),
            header: $this->tabHeaderMapper->mapToView($entity),
            tabs: $tabs,
            fallback: new LinkView(
                url: $fallback,
                icon: 'lucide:chevron-left',
                size: Size::ICON
            ),
        );
    }
}
