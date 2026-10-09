<?php

declare(strict_types=1);

namespace App\State\Product\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\Product\Tab\MainView;
use App\Dto\View\Product\Tab\SizesView;
use App\Dto\View\SheetFormWrapperView;
use App\Dto\View\SheetTabsView;
use App\Dto\View\TabView;
use App\Entity\Product;
use App\Mapper\Product\MediaMapper;

/**
 * @implements FormComponentProviderInterface<Product>
 */
class ProductUpdateProvider implements FormComponentProviderInterface
{
    public function __construct(
        private MediaMapper $mediaMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): SheetFormWrapperView
    {
        $action = ($data->getId()) ? 'Modifier' : 'Ajouter';
        $currentTab = $context->tab;
        return new SheetFormWrapperView(
            title: sprintf('%s une activité', $action),
            description: sprintf('%s un article en définissant les paramètres généreaux, l\'image, les tailles...', $action),
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
                    title: 'Média',
                    index: 2,
                    isActive: 2 === $currentTab,
                    icon: 'lucide:settings-2',
                    view: $this->mediaMapper->mapToView($data),
                ),
                new TabView(
                    title: 'Tailles',
                    index: 3,
                    isActive: 3 === $currentTab,
                    icon: 'lucide:users',
                    view: new SizesView(),
                ),
            ]),
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
            ]
        ];
    }
}
