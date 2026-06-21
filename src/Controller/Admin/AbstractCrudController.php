<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Filter\ListFilterType;
use App\State\DialogProcessorInterface;
use App\State\DialogProviderInterface;
use App\State\FilterInitializerInterface;
use App\State\ListProviderInterface;
use App\State\StreamExportableInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class AbstractCrudController extends AbstractController
{
    protected function handleListAction(
        string $route,
        string $filterClass,
        ListProviderInterface $provider,
        Request $request,
    ): Response {
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

    protected function handleDialogAction(
        Request $request,
        object $object,
        DialogProviderInterface $provider,
        DialogProcessorInterface $processor,
        string $formClass = FormType::class,
        array $formOptions = []
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm($formClass, $object, array_merge([
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ], $formOptions));
        
        $form->handleRequest($request);
        
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $result = $processor->process($object, $request->query->get('filter'));
                $this->addFlash($result->flashType, $result->messageKey);
                
                if ($result->targetRoute) {
                    return $this->redirectToRoute($result->targetRoute, $result->routeParams);
                }
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('components/_dialog.modal.html.twig', [
            'form' => $form->createView(),
            'dialog' => $provider->mapToView($object)
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
