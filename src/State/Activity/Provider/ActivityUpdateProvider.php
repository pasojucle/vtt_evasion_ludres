<?php

declare(strict_types=1);

namespace App\State\Activity\Provider;

use App\Dto\Enum\Size;
use App\Dto\State\ViewContext;
use App\Dto\View\Activity\Tab\MainView;
use App\Dto\View\Activity\Tab\OptionsView;
use App\Dto\View\Activity\Tab\ParticipantsView;
use App\Dto\View\Activity\Tab\TracksView;
use App\Dto\View\ButtonView;
use App\Dto\View\FormTabWrapperView;
use App\Dto\View\LinkView;
use App\Dto\View\TabView;
use App\Entity\BikeRide;
use App\Mapper\Activity\Update\MediasMapper;
use App\Service\MessageService;
use App\State\Interface\FormComponentProviderInterface;
use App\State\Interface\InputInitializerInterface;

/**
 * @implements FormComponentProviderInterface<BikeRide>
 */
class ActivityUpdateProvider implements FormComponentProviderInterface, InputInitializerInterface
{
    public function __construct(
        private MediasMapper $mediasMapper,
        private MessageService $messageService,
    ) {
    }

    public function getFormView(object $entity, ?ViewContext $context = null): FormTabWrapperView
    {
        $action = ($entity->getId()) ? 'Modifier' : 'Ajouter';
        $currentTab = $context->tab;

        return new FormTabWrapperView(
            name: 'activityEdit',
            title: sprintf('%s une activité', $action),
            description: sprintf('%s une activité en définissant les paramètres généreaux, des participants, des médias, des parcours...', $action),
            tabs: [
                new TabView(
                    title: 'Général',
                    index: 1,
                    isActive: 1 === $currentTab,
                    icon: 'lucide:info',
                    view: new MainView(),
                ),
                new TabView(
                    title: 'Options',
                    index: 2,
                    isActive: 2 === $currentTab,
                    icon: 'lucide:settings-2',
                    view: new OptionsView(),
                ),
                new TabView(
                    title: 'Participants',
                    index: 3,
                    isActive: 3 === $currentTab,
                    icon: 'lucide:users',
                    view: new ParticipantsView(),
                ),
                new TabView(
                    title: 'Médias',
                    index: 4,
                    isActive: 4 === $currentTab,
                    icon: 'lucide:image',
                    view: $this->mediasMapper->mapToView($entity),
                ),
                new TabView(
                    title: 'Parcours GPX',
                    index: 5,
                    isActive: 5 === $currentTab,
                    icon: 'lucide:map',
                    view: new TracksView(),
                ),
            ],
            fallback: new LinkView(
                url: $context->fallback,
                icon: 'lucide:chevron-left',
                size: Size::ICON
            ),
            submit: new ButtonView(
                label: $action,
                icon: 'lucide:square-check-big'
            ),
        );
    }

    public function getFormOptions(object $entity): array
    {
        return [];
    }

    /**
     * @param BikeRide $entity
     * @return BikeRide
     */
    public function setDefaultValues(object $entity): object
    {
        if (null === $entity->getId()) {
            $entity->setRegistrationClosedMessage(
                $this->messageService->getMessageById('REGISTRATION_CLOSED_DEFAULT_MESSAGE')
            );
        }

        return $entity;
    }
}
