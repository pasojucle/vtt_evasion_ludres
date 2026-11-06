<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Provider\FilterInitializerInterface;
use App\Core\Contract\Provider\ListLoadMoreProviderInterface;
use App\Core\Contract\View\LoadMoreViewInterface;
use App\Core\Dto\HandlerContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

readonly class ListLoadMoreHandler
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public function handle(
        Request $request,
        string $filterClass,
        ListLoadMoreProviderInterface $provider,
        ?object $parent = null,
    ): Response {
        $context = HandlerContext::fromRequest($request, $parent);
        $filter = $provider->getHydratedDto($context->queryParams, $filterClass);
        if ($provider instanceof FilterInitializerInterface) {
            $provider->initializeFilters($filter, $context);
        }

        $currentPage = $context->page;
        $view = $provider->getStreamView($filter, null, $context);

        if (1 < $currentPage) {
            return new Response(
                $this->renderView($view->getStreamLoadMoreTemplate(), $view),
                Response::HTTP_OK,
                ['Content-Type' => 'text/vnd.turbo-stream.html', ]
            );
        }

        return new Response(
            $this->renderView($view->getStreamTemplate(), $view),
            Response::HTTP_OK,
            ['Content-Type' => 'text/vnd.turbo-stream.html', ]
        );
    }

    private function renderView(string $template, LoadMoreViewInterface $view): string
    {
        return $this->twig->render($template, [
            'view' => $view
        ]);
    }
}
