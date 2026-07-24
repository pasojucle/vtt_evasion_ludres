<?php

declare(strict_types=1);

namespace App\State\Activity\Provider;

use App\Dto\Enum\Size;
use App\Dto\View\ButtonView;
use App\Dto\View\LinkView;
use App\Dto\View\FormTabWrapperView;
use App\Dto\View\TabView;
use App\Entity\BikeRide;
use App\Mapper\Activity\ActivityUpdateMapper;
use App\Service\MessageService;
use App\State\Interface\FormAddComponentProviderInterface;
use App\State\Interface\FormComponentProviderInterface;

/**
 * @implements FormComponentProviderInterface<BikeRide>
 */
class ActivityUpdateProvider implements FormComponentProviderInterface, FormAddComponentProviderInterface
{
    public function __construct(
        private ActivityUpdateMapper $mapper,
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
                new TabView('Général', 'lucide:info', 'bike_ride/admin/edit/tab_general.html.twig'),
                new TabView('Options', 'lucide:settings-2', 'bike_ride/admin/edit/tab_option.html.twig'),
                new TabView('Participants', 'lucide:users', 'bike_ride/admin/edit/tab_participant.html.twig', ),
                new TabView('Médias', 'lucide:image', 'bike_ride/admin/edit/tab_media.html.twig'),
                new TabView('Parcours GPX', 'lucide:map', 'bike_ride/admin/edit/tab_gpx.html.twig'),
            ],
            entity: $this->mapper->mapToView($entity),
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
