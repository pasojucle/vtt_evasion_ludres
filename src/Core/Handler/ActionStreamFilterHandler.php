<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Provider\ListLoadMoreProviderInterface;
use App\Core\Contract\View\ComponentFormViewInterface;
use App\Core\Dto\HandlerContext;
use App\Form\Filter\FilterAdvancedType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

class ActionStreamFilterHandler
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private Environment $twig,
    ){}

    public function handle(
        Request $request,
        object $data,
        ListLoadMoreProviderInterface $provider,
    ): Response {
        $context = HandlerContext::fromRequest($request, $data);

        $filterConfig = $provider->getFilterConfig($context->route);
        if (!$filterConfig) {
            throw new NotFoundHttpException();
        }
        $dataClass = $filterConfig->getDataClass();
        $filter = $provider->getHydratedDto($context->queryParams, $dataClass);

        $form = $this->formFactory->create(FilterAdvancedType::class, $filter, [
            'action' => $request->getPathInfo(),
            'filter_config' => $filterConfig,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $streamView = $provider->getStreamView($filter, null, $context);
            return new Response(
                $this->twig->render($streamView->getStreamTemplate(),
                    ['view' => $streamView]
                ),
                Response::HTTP_OK,
                ['Content-Type' => 'text/vnd.turbo-stream.html', ]
            );
        }

        return new Response($this->renderView($provider->getView($data, $context), $form));
    }

    private function renderView(ComponentFormViewInterface $view, FormInterface $form): string
    {
        return $this->twig->render($view->getTemplate(), [
            'form' => $form->createView(),
            'view' => $view
        ]);
    }
}