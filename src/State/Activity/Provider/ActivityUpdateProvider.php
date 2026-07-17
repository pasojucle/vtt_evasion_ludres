<?php

declare(strict_types=1);

namespace App\State\Activity\Provider;

use App\Dto\View\FormUpdateView;
use App\Dto\View\TabView;
use App\Entity\BikeRide;
use App\Mapper\Activity\ActivityEditMapper;
use App\Service\MessageService;
use App\State\Interface\FormAddComponentProviderInterface;
use App\State\Interface\FormComponentProviderInterface;

/**
 * @implements FormComponentProviderInterface<BikeRide>
 */
class ActivityUpdateProvider implements FormComponentProviderInterface, FormAddComponentProviderInterface
{
    public function __construct(
        private ActivityEditMapper $mapper,
        private MessageService $messageService,
    ) {
    }

    public function mapToView(object $entity): FormUpdateView
    {
        return new FormUpdateView(
            name: 'activityEdit',
            title: ($entity->getId()) ? 'Modifier une activité' : 'Ajouter une activité',
            description: 'Ajouter une activité en définissant les paramètres généreaux, des participants, des médias, des parcours...',
            tabs: [
                new TabView('Général', 'lucide:info', 'bike_ride/admin/edit/tab_general.html.twig'),
                new TabView('Options', 'lucide:settings-2', 'bike_ride/admin/edit/tab_option.html.twig'),
                new TabView('Participants', 'lucide:image', 'bike_ride/admin/edit/tab_participant.html.twig', ),
                new TabView('Médias', 'lucide:image', 'bike_ride/admin/edit/tab_media.html.twig'),
                new TabView('Parcours GPX', 'lucide:map', 'bike_ride/admin/edit/tab_gpx.html.twig'),
            ],
            entity: $this->mapper->mapToView($entity),
            back: null,
        );
    }

    /**
     * @param ?BikeRide $entity
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
