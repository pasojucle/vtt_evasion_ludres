<?php

declare(strict_types=1);

namespace App\Http\Handler;

use App\Form\Filter\ListFilterType;
use App\State\Interface\FilterInitializerInterface;
use App\State\Interface\ListProviderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

readonly class ListPaginedHandler
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
        $queryParams = $request->query->all();

        $filterConfig = $provider->getFilterConfig($route);
        if (!$filterConfig) {
            throw new NotFoundHttpException();
        }

        $dataClass = $filterConfig->getDataClass();
        $filter = $provider->getHydratedDto($queryParams, $dataClass);
        if ($provider instanceof FilterInitializerInterface) {
            $provider->initializeFilters($filter, $queryParams);
        }

        $form = $this->formFactory->create(ListFilterType::class, $filter, [
            'filter_config' => $filterConfig,
        ]);

        $form->handleRequest($request);

        return new Response($this->renderView($provider, $form, $filter, $filterConfig, $route, $request->query->getInt('page', 1)));
    }

    private function renderView($provider, $form, $filter, $filterConfig, $route, int $page): string
    {
        return $this->twig->render('components/list/_list.html.twig', [
            'form' => $form->createView(),
            'list' => $provider->getCollection(
                $filter,
                $filterConfig,
                $route,
                $page,
            ),
        ]);
    }
}
