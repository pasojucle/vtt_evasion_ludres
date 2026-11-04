<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Provider\ListLoadMoreProviderInterface;
use App\Core\Contract\View\LoadMoreViewInterface;
use App\Core\Dto\HandlerContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ActionStreamDeleteFilterHandler
{
    public function __construct(
        private Environment $twig,
    ){}

    public function handle(
        Request $request,
        object $data,
        string $filterClass,
        ListLoadMoreProviderInterface $provider,
    ): Response {
        $context = HandlerContext::fromRequest($request, $data);

        $filter = $provider->getHydratedDto($context->queryParams, $filterClass);

        $streamView = $provider->getStreamView($filter, null, $context);
        return new Response(
            $this->renderView($streamView),
            Response::HTTP_OK,
            ['Content-Type' => 'text/vnd.turbo-stream.html', ]
        );
    }

    private function renderView(LoadMoreViewInterface $view): string
    {
        return $this->twig->render($view->getStreamTemplate(), [
            'view' => $view
        ]);
    }
}