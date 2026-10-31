<?php

declare(strict_types=1);

namespace App\State\Agreement\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\Agreement\Tab\KindView;
use App\Dto\View\Agreement\Tab\MainView;
use App\Dto\View\Agreement\Tab\SettingsView;
use App\Dto\View\SheetFormWrapperView;
use App\Dto\View\SheetTabsView;
use App\Dto\View\TabView;
use App\Entity\BikeRide;

/**
 * @implements FormComponentProviderInterface<BikeRide>
 */
class AgreementUpdateProvider implements FormComponentProviderInterface
{
    public function getView(object $data, ?HandlerContext $context = null): SheetFormWrapperView
    {
        $action = ($data->getId()) ? 'Modifier' : 'Ajouter';

        $currentTab = $context->tab;

        return new SheetFormWrapperView(
            title: sprintf('%s une autorisation', $action),
            description: sprintf('%s une autorisation en définissant le nom', $action),
            action: $action,
            frameId: sprintf('agreement-update-%s', $data->getId() ?? ''),
            formView: new SheetTabsView([
                new TabView(
                    title: 'Général',
                    index: 1,
                    isActive: 1 === $currentTab,
                    icon: 'lucide:info',
                    view: new MainView(),
                ),
                new TabView(
                    title: 'Paramètres',
                    index: 2,
                    isActive: 2 === $currentTab,
                    icon: 'lucide:settings-2',
                    view: new SettingsView(),
                ),
                new TabView(
                    title: 'Type',
                    index: 3,
                    isActive: 3 === $currentTab,
                    icon: 'lucide:square-check',
                    view: new KindView(),
                ),
                
            ]),
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-controller' => 'form-modifier'
            ]
        ];
    }
}
