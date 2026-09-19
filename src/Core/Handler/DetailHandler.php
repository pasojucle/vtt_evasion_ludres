<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\View\ComponentViewInterface;
use App\Dto\State\ViewContext;
use App\State\Interface\ComponentProviderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

readonly class DetailHandler
{
    public function __construct(
        private FormFactoryInterface $formFactory,
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
            new ViewContext(
                route: $request->attributes->get('_route'),
                routeParams: $request->attributes->get('_route_params'),
                encodedFallback: $request->query->get('_redirect_to'),
                tab: $request->query->getInt('tab', 1),
            ),
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
