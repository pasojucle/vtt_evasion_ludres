<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\Provider\FilterInitializerInterface;
use App\Core\Contract\Provider\ListProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Filter\AbstractFilter;
use App\Form\Filter\ListFilterType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

readonly class ListPaginatedHandler
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private Environment $twig,
    ) {
    }

    public function handle(
        Request $request,
        ListProviderInterface $provider,
    ): Response {
        $route = $request->attributes->get('_route');
        $context = HandlerContext::fromRequest($request);

        $filterConfig = $provider->getFilterConfig($route);
        if (!$filterConfig) {
            throw new NotFoundHttpException();
        }

        $dataClass = $filterConfig->getDataClass();
        $filter = $provider->getHydratedDto($context->queryParams, $dataClass);
        if ($provider instanceof FilterInitializerInterface) {
            $provider->initializeFilters($filter, $context->queryParams);
        }

        $form = $this->formFactory->create(ListFilterType::class, $filter, [
            'filter_config' => $filterConfig,
        ]);

        $form->handleRequest($request);

        return new Response($this->renderView($provider, $form, $filter, $filterConfig, $context));
    }

    private function renderView(
        ListProviderInterface $provider,
        FormInterface $form,
        AbstractFilter $filter,
        FilterConfigInterface $filterConfig,
        HandlerContext $context
    ): string {
        return $this->twig->render('components/list/_list.html.twig', [
            'form' => $form->createView(),
            'list' => $provider->getCollection(
                $filter,
                $filterConfig,
                $context,
            ),
        ]);
    }
}
