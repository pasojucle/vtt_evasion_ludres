<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Filter\AbstractFilter;
use App\State\DialogProcessorInterface;
use App\State\DialogProviderInterface;
use App\State\ListProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class AbstractCrudController extends AbstractController
{
    protected function handleListAction(
        AbstractFilter $filter,
        string $formClass,
        ListProviderInterface $provider,
        string $template,
        Request $request,
    ): Response {
        $filterConfig = $provider->getFilterConfig('admin_registration_list');
        if (!$filterConfig) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm($formClass, $filter, [
            'data_class' => $filterConfig->getDataClass(),
            'fields' => $filterConfig->getFields(),
            'advanced_fields' => $filterConfig->getAdvancedFields(),
            'event_subscriber' => $filterConfig->getEventSubscriber(),
        ]);

        $form->handleRequest($request);

        return $this->render($template, [
            'form' => $form->createView(),
            'list' => $provider->getCollection(
                $filter,
                $filterConfig,
                $request->attributes->get('_route'),
                $request->query->getInt('page', 1),
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
        AbstractFilter $filter,
        ListProviderInterface $provider,
    ): StreamedResponse {
        $response = new StreamedResponse(function() use ($provider, $filter) {
            $provider->streamExportContent($filter);
        });
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export_inscriptions.csv"');

        return $response;
    }
}