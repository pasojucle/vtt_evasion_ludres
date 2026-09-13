<?php

declare(strict_types=1);

namespace App\Http\Handler;

use App\Dto\Filter\AbstractFilter;
use App\Dto\State\ViewContext;
use App\Form\Filter\ListFilterType;
use App\State\Interface\FilterInitializerInterface;
use App\State\Interface\ListDrawerProviderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

readonly class ListDrawerHandler
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private Environment $twig,
    ) {
    }

    public function handle(
        Request $request,
        ListDrawerProviderInterface $provider,
        object $entity
    ): Response {
        $route = $request->attributes->get('_route');
        $filterConfig = $provider->getFilterConfig($route);
        if (!$filterConfig) {
            throw new NotFoundHttpException();
        }

        $queryParams = $request->query->all();
        $filter = $provider->getHydratedDto($queryParams, $filterConfig->getDataClass());
        if ($provider instanceof FilterInitializerInterface) {
            $provider->initializeFilters($filter, $queryParams);
        }

        $context = new ViewContext(
            $request->attributes->get('_route'),
            $request->attributes->get('_route_params'),
            1,
            $entity,
            $request->query->get('_redirect_to'),
        );
        $form = $this->formFactory->create(ListFilterType::class, $filter, [
            'action' => $request->getUri(),
            'filter_config' => $filterConfig,
        ]);

        $form->handleRequest($request);

        return new Response($this->renderView($provider, $form, $filter, $context));
    }

    private function renderView(
        ListDrawerProviderInterface $provider,
        FormInterface $form,
        AbstractFilter $filter,
        ViewContext $context
    ): string {
        $view = $provider->getCollection($filter, $context);
        $formView = $form->createView();
        $formView->vars['attr'] = array_merge($formView->vars['attr'] ?? [], $view->getFormAttr());
        return $this->twig->render($view->getTemplate(), [
            'form' => $formView,
            'view' => $view
        ]);
    }
}
