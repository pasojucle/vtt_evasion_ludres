<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Filter\ListFilterType;
use App\Service\UrlContextService;
use App\State\DialogProcessorInterface;
use App\State\FilterInitializerInterface;
use App\State\FormComponentProviderInterface;
use App\State\ListProviderInterface;
use App\State\StreamExportableInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
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

    protected function handleListAction(
        string $filterClass,
        ListProviderInterface $provider,
        Request $request,
    ): Response {
        $route = $request->attributes->get('_route');

        $filter = $provider->getHydratedDto($request->query->all(), $filterClass);
        if ($provider instanceof FilterInitializerInterface) {
            $provider->initializeFilters($filter);
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
                $request->attributes->get('_route'),
                $request->query->getInt('page', 1)
            ),
        ]);
    }

    protected function handleFormComponentAction(
        Request $request,
        object $object,
        FormComponentProviderInterface $provider,
        DialogProcessorInterface $processor,
        string $formClass = FormType::class,
        array $formOptions = []
    ): Response {
        $view = $provider->mapToView($object);

        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm($formClass, $object, array_merge([
            'action' => $request->getUri(),
            'attr' => $view->getFormAttr(),
        ], $formOptions));
        
        $form->handleRequest($request);
        dump($this->urlContextService->getRedirectUrl($request));
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $result = $processor->process($object, $this->urlContextService->getRedirectUrl($request));
                $this->addFlash($result->flashType, $result->messageKey);
                if ($result->targetUrl) {
                    return $this->redirect($result->targetUrl, Response::HTTP_SEE_OTHER);
                }
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render($view->getTemplate(), [
            'form' => $form->createView(),
            'view' => $view
        ], $response);
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
}
