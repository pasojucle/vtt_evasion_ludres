<?php

declare(strict_types=1);

namespace App\Controller;

use App\State\DialogProcessorInterface;
use App\State\DialogProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCrudController extends AbstractController
{
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
}