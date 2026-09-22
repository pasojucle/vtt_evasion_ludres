<?php

declare(strict_types=1);

namespace App\State\User\Provider;

use App\Core\Contract\Provider\ComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\Size;
use App\Dto\State\ViewContext;
use App\Dto\View\LinkView;
use App\Dto\View\TabView;
use App\Dto\View\TabWrapperView;
use App\Entity\User;
use App\Mapper\User\Read\IdentityMapper;
use App\Mapper\User\Read\LicenceMapper;
use App\Mapper\User\Read\ParticipationMapper;
use App\Mapper\User\Read\SkillsMapper;
use App\Mapper\User\Read\TabHeaderMapper;
use App\Service\SeasonService;
use App\Service\UrlContextService;

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
        private UrlContextService $urlContext,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): TabWrapperView
    {
        $currentTab = $context->tab;
        $tabs = [
            new TabView(
                title: 'Identité & Contacts',
                icon: 'lucide:user',
                view: $this->identityMapper->mapToView($data),
                index: 1,
                isActive: 1 === $currentTab,
            ),
            new TabView(
                title: 'Licence & statut',
                icon: 'lucide:id-card',
                view: $this->licenceMapper->mapToView($data),
                index: 2,
                isActive: 2 === $currentTab,
            ),
            new TabView(
                title: 'Participation',
                icon: 'lucide:chart-line',
                view: $this->participationMapper->mapToView(
                    $data,
                    $this->seasonService->getCurrentSeasonPeriod()
                ),
                index: 3,
                isActive: 3 === $currentTab,
            ),
        ];
        if ($data->isSchoolMember()) {
            $tabs[] = new TabView(
                title: 'Compétences',
                icon: 'lucide:badge-check',
                view: $this->skillsMapper->mapToView($data),
                index: 4,
                isActive: 4 === $currentTab,
            );
        }

        return new TabWrapperView(
            name: sprintf('user-%s', $data->getId()),
            title: $data->getIdentity()->getFullName(),
            header: $this->tabHeaderMapper->mapToView($data),
            tabs: $tabs,
            fallback: new LinkView(
                url: $this->urlContext->decodeUrl($context->encodedFallback),
                icon: 'lucide:chevron-left',
                size: Size::ICON
            ),
        );
    }
}
