<?php

declare(strict_types=1);

namespace App\State\ActivityType\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\ActivityType\Tab\RegistrationView;
use App\Dto\View\ActivityType\Tab\MainView;
use App\Dto\View\ActivityType\Tab\OptionsView;
use App\Dto\View\SheetFormWrapperView;
use App\Dto\View\SheetTabsView;
use App\Dto\View\TabView;
use App\Entity\BikeRide;

/**
 * @implements FormComponentProviderInterface<BikeRide>
 */
class ActivityTypeUpdateProvider implements FormComponentProviderInterface
{
    public function getView(object $data, ?HandlerContext $context = null): SheetFormWrapperView
    {
        $action = ($data->getId()) ? 'Modifier' : 'Ajouter';
        $currentTab = $context->tab;

        return new SheetFormWrapperView(
            title: sprintf('%s un type activité', $action),
            description: sprintf('%s un type activité en définissant les paramètres généreaux, les groupes et les options', $action),
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
                    title: 'Inscription',
                    index: 2,
                    isActive: 2 === $currentTab,
                    icon: 'lucide:ticket-plus',
                    view: new RegistrationView(),
                ),
                new TabView(
                    title: 'Options',
                    index: 3,
                    isActive: 3 === $currentTab,
                    icon: 'lucide:settings-2',
                    view: new OptionsView(),
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
}
