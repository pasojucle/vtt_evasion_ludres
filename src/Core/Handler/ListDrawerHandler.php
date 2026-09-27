<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Provider\FilterInitializerInterface;
use App\Core\Contract\Provider\ListDrawerProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Filter\AbstractFilter;
use App\Form\Filter\ListFilterType;
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
        $context = HandlerContext::fromRequest($request, $entity);

        $filterConfig = $provider->getFilterConfig($context->route);
        if (!$filterConfig) {
            throw new NotFoundHttpException();
        }

        $filter = $provider->getHydratedDto($context->queryParams, $filterConfig->getDataClass());
        if ($provider instanceof FilterInitializerInterface) {
            $provider->initializeFilters($filter, $context->queryParams);
        }

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
        HandlerContext $context
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
