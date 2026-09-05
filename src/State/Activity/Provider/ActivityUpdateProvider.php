<?php

declare(strict_types=1);

namespace App\State\Activity\Provider;

use App\Dto\Enum\Size;
use App\Dto\View\Activity\Tab\MainView;
use App\Dto\View\Activity\Tab\MediasView;
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
use App\State\Interface\FormAddComponentProviderInterface;
use App\State\Interface\FormComponentProviderInterface;

/**
 * @implements FormComponentProviderInterface<BikeRide>
 */
class ActivityUpdateProvider implements FormComponentProviderInterface, FormAddComponentProviderInterface
{
    public function __construct(
        private MediasMapper $mediasMapper,
        private MessageService $messageService,
    ) {
    }

    public function getFormView(object $entity, ?string $fallback = null): FormTabWrapperView
    {
        $action = ($entity->getId()) ? 'Modifier' : 'Ajouter';

        return new FormTabWrapperView(
            name: 'activityEdit',
            title: sprintf('%s une activité', $action),
            description: sprintf('%s une activité en définissant les paramètres généreaux, des participants, des médias, des parcours...', $action),
            tabs: [
                new TabView(
                    title: 'Général', 
                    icon: 'lucide:info', 
                    view: new MainView(),
                ),
                new TabView(
                    title: 'Options', 
                    icon: 'lucide:settings-2', 
                    view: new OptionsView(),
                ),
                new TabView(
                    title: 'Participants', 
                    icon: 'lucide:users', 
                    view: new ParticipantsView(),
                ),
                new TabView(
                    title: 'Médias', 
                    icon: 'lucide:image', 
                    view: $this->mediasMapper->mapToView($entity),
                ),
                new TabView(
                    title: 'Parcours GPX', 
                    icon: 'lucide:map', 
                    view: new TracksView(),
                ),
            ],
            fallback: new LinkView(
                url: $fallback,
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
        return [
            'attr' => [
                'data-controller' => 'form-modifier'
            ]
        ];
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
