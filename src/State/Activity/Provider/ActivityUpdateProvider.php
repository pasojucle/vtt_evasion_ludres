<?php

declare(strict_types=1);

namespace App\State\Activity\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\InputInitializerInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\Activity\Tab\MainView;
use App\Dto\View\Activity\Tab\OptionsView;
use App\Dto\View\Activity\Tab\ParticipantsView;
use App\Dto\View\Activity\Tab\TracksView;
use App\Dto\View\SheetFormWrapperView;
use App\Dto\View\SheetTabsView;
use App\Dto\View\TabView;
use App\Entity\BikeRide;
use App\Mapper\Activity\Update\MediasMapper;
use App\Service\MessageService;

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

    public function getView(object $data, ?HandlerContext $context = null): SheetFormWrapperView
    {
        $action = ($data->getId()) ? 'Modifier' : 'Ajouter';
        $currentTab = $context->tab;

        return new SheetFormWrapperView(
            title: sprintf('%s une activité', $action),
            description: sprintf('%s une activité en définissant les paramètres généreaux, des participants, des médias, des parcours...', $action),
            action: $action,
            frameId: sprintf('product-update-%s', $data->getId() ?? ''),
            formView: new SheetTabsView([
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
                    view: $this->mediasMapper->mapToView($data),
                ),
                new TabView(
                    title: 'Parcours GPX',
                    index: 5,
                    isActive: 5 === $currentTab,
                    icon: 'lucide:map',
                    view: new TracksView(),
                ),
            ]),
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-controller' => 'form-modifier',
            ],
        ];
    }

    /**
     * @param BikeRide $data
     * @return BikeRide
     */
    public function setDefaultValues(object $data): object
    {
        if (null === $data->getId()) {
            $data->setRegistrationClosedMessage(
                $this->messageService->getMessageById('REGISTRATION_CLOSED_DEFAULT_MESSAGE')
            );
        }

        return $data;
    }
}
