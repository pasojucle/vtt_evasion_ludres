<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\Filter\AbstractFilter;
use App\Dto\State\RedirectProcessorResult;
use App\Dto\State\TurboStreamContext;
use App\Dto\State\TurboStreamProcessorResult;
use App\Form\Filter\FilterAdvancedType;
use App\Form\Filter\ListFilterType;
use App\Service\UrlContextService;
use App\State\Interface\ComponentProcessorInterface;
use App\State\Interface\ComponentProviderInterface;
use App\State\Interface\FilterInitializerInterface;
use App\State\Interface\FormAddComponentProviderInterface;
use App\State\Interface\FormComponentProviderInterface;
use App\State\Interface\FormProcessorInterface;
use App\State\Interface\FormRedirectProcessorInterface;
use App\State\Interface\JsonProcessorInterface;
use App\State\Interface\ListLoadMoreProviderInterface;
use App\State\Interface\ListProviderInterface;
use App\State\Interface\StreamExportableInterface;
use App\State\Interface\TurboStreamProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractCrudController extends AbstractController
{
    protected UrlContextService $urlContextService;

    #[Required()]
    public function setUrlContextService(UrlContextService $urlContextService): void
    {
        $this->urlContextService = $urlContextService;
    }

    protected function handleListPaginedAction(
        string $filterClass,
        ListProviderInterface $provider,
        Request $request,
    ): Response {
        $route = $request->attributes->get('_route');
        $queryParams = $request->query->all();

        $filter = $provider->getHydratedDto($queryParams, $filterClass);
        if ($provider instanceof FilterInitializerInterface) {
            $provider->initializeFilters($filter, $queryParams);
        }

        $filterConfig = $provider->getFilterConfig($route);
        if (!$filterConfig) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ListFilterType::class, $filter, [
            'data_class' => $filterConfig->getDataClass(),
            'fields' => $filterConfig->getFields(),
            'advanced_fields' => $filterConfig->getAdvancedFields(),
            'event_subscriber' => $filterConfig->getEventSubscriber(),
        ]);

        $form->handleRequest($request);

        return $this->render('components/list/_list.html.twig', [
            'form' => $form->createView(),
            'list' => $provider->getCollection(
                $filter,
                $filterConfig,
                $route,
                $request->query->getInt('page', 1),
            ),
        ]);
    }

    protected function handleListLoadMoreAction(
        Request $request,
        string $filterClass,
        ListLoadMoreProviderInterface $provider,
        TurboStreamContext $context
    ): Response {
        $queryParams = $request->query->all();
        $filter = $provider->getHydratedDto($queryParams, $filterClass);

        $currentPage = $context->page;
        $view = $provider->getStreamView($filter, $context);
        if (1 < $currentPage) {
            return $this->render($view->getStreamLoadMoreTemplate(), [
                'view' => $view,
                ], new Response('', Response::HTTP_OK, [
                    'Content-Type' => 'text/vnd.turbo-stream.html',
                ]));
        }

        return $this->render($view->getTemplate(), [
            'view' => $view,
        ]);
    }

    protected function handleFormComponentAction(
        Request $request,
        object $object,
        FormComponentProviderInterface $provider,
        FormProcessorInterface $processor,
        string $formClass = FormType::class,
        ?TurboStreamContext $context = null,
    ): Response {
        $fallback = $this->urlContextService->getRedirectUrl($request);
        if ($provider instanceof FormAddComponentProviderInterface) {
            $provider->setDefaultValues($object);
        }

        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm($formClass, $object, array_merge([
            'action' => $request->getUri(),
        ], $provider->getFormOptions($object)));
        
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $result = $processor->process(
                    $object,
                    $request->files->get($form->getName()),
                    $fallback,
                );
                if ($result instanceof RedirectProcessorResult) {
                    $this->addFlash($result->flashType, $result->messageKey);
                
                    return $this->redirect($result->targetUrl, Response::HTTP_SEE_OTHER);
                }
                if ($result instanceof TurboStreamProcessorResult && $provider instanceof TurboStreamProviderInterface) {
                    $streamView = $provider->getStreamView($object, $context);

                    return $this->render($streamView->getStreamTemplate(), [
                            'view' => $streamView,
                        ], new Response('', Response::HTTP_OK, [
                            'Content-Type' => 'text/vnd.turbo-stream.html',
                        ]));
                }
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $view = $provider->getFormView($object, $fallback);
        $formView = $form->createView();
        $formView->vars['attr'] = array_merge($formView->vars['attr'] ?? [], $view->getFormAttr());
        return $this->render($view->getTemplate(), [
            'form' => $form->createView(),
            'view' => $view
        ], $response);
    }

    protected function handleProcessAction(
        Request $request,
        object $object,
        FormRedirectProcessorInterface $processor,
    ): Response {
        $result = $processor->process(
            $object,
            null,
            $this->urlContextService->getRedirectUrl($request)
        );
        
        $this->addFlash($result->flashType, $result->messageKey);
    
        return $this->redirect($result->targetUrl, Response::HTTP_SEE_OTHER);
    }

    protected function handleComponentProcessAction(
        object $object,
        ComponentProcessorInterface $processor,
    ): Response {
        $result = $processor->process($object);
        
        $this->addFlash($result->flashType, $result->messageKey);

        $component = $result->component;
        return $this->render($component->getTemplate(), [
            $component->getName() => $component,
        ]);
    }

    protected function handleJsonProcessAction(
        Request $request,
        object $object,
        JsonProcessorInterface $processor,
    ): JsonResponse {
        $result = $processor->process($object);

        return new JsonResponse([
            'success' => $result->success,
            'data' => $result->data
        ], $result->success ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }

    protected function handleExportAction(
        Request $request,
        string $filterClass,
        StreamExportableInterface $provider,
        string $filename,
    ): StreamedResponse {
        $filter = $provider->getHydratedDto($request->query->all(), $filterClass);

        $response = new StreamedResponse(function () use ($provider, $filter) {
            $provider->streamExportContent($filter);
        });
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        return $response;
    }

    protected function handleComponentAction(
        Request $request,
        ComponentProviderInterface $provider,
        object $object,
    ): Response {
        $fallback = $this->urlContextService->getRedirectUrl($request);

        $view = $provider->getView($object, $fallback);
        return $this->render($view->getTemplate(), [
            'view' => $view
        ]);
    }

    protected function handleStreamFilterAction(
        Request $request,
        object $entity,
        ListLoadMoreProviderInterface $provider,
    ): Response {
        $route = $request->attributes->get('_route');
        $queryParams = $request->query->all();

        $filterConfig = $provider->getFilterConfig($route);
        if (!$filterConfig) {
            throw $this->createNotFoundException();
        }
        $dataClass = $filterConfig->getDataClass();
        $filter = $provider->getHydratedDto($queryParams, $dataClass);

        $form = $this->createForm(FilterAdvancedType::class, $filter, [
            'action' => $request->getPathInfo(),
            'fields' => $filterConfig->getFields(),
            'advanced_fields' => $filterConfig->getAdvancedFields(),
            'data_class' => $dataClass,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $streamView = $provider->getStreamView($filter, new TurboStreamContext(
                $request->attributes->get('_route'),
                $request->query->getInt('page', 1),
                $entity
            ));

            return $this->render($streamView->getStreamTemplate(), [
                    'view' => $streamView,
                ], new Response('', Response::HTTP_OK, [
                    'Content-Type' => 'text/vnd.turbo-stream.html',
                ]));
        }

        return $this->render('components/_form.sheet.html.twig', [
            'view' => $provider->getFormView($entity),
            'form' => $form->createView(),
        ]);
    }

    protected function handleStreamFilterDeleteAction(
        Request $request,
        object $entity,
        ListLoadMoreProviderInterface $provider,
        string $filterRoute,
    ): Response {
        $queryParams = $request->query->all();

        $filterConfig = $provider->getFilterConfig($filterRoute);
        if (!$filterConfig) {
            throw $this->createNotFoundException();
        }
        $dataClass = $filterConfig->getDataClass();
        $filter = $provider->getHydratedDto($queryParams, $dataClass);

        $streamView = $provider->getStreamView($filter, new TurboStreamContext(
            $filterRoute,
            1,
            $entity
        ));

        return $this->render($streamView->getStreamTemplate(), [
                'view' => $streamView,
            ], new Response('', Response::HTTP_OK, [
                'Content-Type' => 'text/vnd.turbo-stream.html',
            ]));
    }
}
