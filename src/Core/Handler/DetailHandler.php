<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Provider\ComponentProviderInterface;
use App\Core\Contract\View\ComponentViewInterface;
use App\Core\Dto\HandlerContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

readonly class DetailHandler
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public function handle(
        Request $request,
        ComponentProviderInterface $provider,
        object $object,
    ): Response {
        $view = $provider->getView(
            $object,
            HandlerContext::fromRequest($request),
        );

        return new Response($this->renderView($view));
    }

    private function renderView(ComponentViewInterface $view): string
    {
        return $this->twig->render($view->getTemplate(), [
            'view' => $view
        ]);
    }
}
