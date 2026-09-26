<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Processor\ProcessorInterface;
use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\InputInitializerInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Contract\View\ComponentFormViewInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Core\Dto\TurboStreamProcessorResult;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Twig\Environment;

readonly class ActionFormHandler
{
    public function __construct(
        private Environment $twig,
        private RequestStack $requestStack,
        private FormFactoryInterface $formFactory,
    ) {
    }

    public function handle(
        Request $request,
        object $data,
        FormComponentProviderInterface $provider,
        ProcessorInterface $processor,
        string $formClass = FormType::class,
    ): Response {
        $context = HandlerContext::fromRequest($request);
        if ($provider instanceof InputInitializerInterface) {
            $provider->setDefaultValues($data);
        }

        $form = $this->formFactory->create($formClass, $data, array_merge([
            'action' => $request->getUri(),
        ], $provider->getFormOptions($data)));
        
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $result = $processor->process(
                    new ActionPayload(
                        $data,
                        $request->files->get($form->getName(), [])
                    ),
                    $context,
                );
                if ($result instanceof RedirectProcessorResult) {
                    $this->addFlash($result->flashType, $result->messageKey);
                
                    return new RedirectResponse($result->targetUrl, Response::HTTP_SEE_OTHER);
                }
                if ($result instanceof TurboStreamProcessorResult && $provider instanceof TurboStreamProviderInterface) {
                    $flash = $result->messageKey ? new FlashMessage($result->flashType, $result->messageKey) : null;
                    $streamView = $provider->getStreamView($result->data, $flash, $context);

                    return new Response(
                        $this->twig->render($streamView->getStreamTemplate(), [
                            'view' => $streamView,
                        ]),
                        Response::HTTP_OK,
                        ['Content-Type' => 'text/vnd.turbo-stream.html'],
                    );
                }
            }
            return new Response(
                $this->renderView($provider->getView($data, $context), $form),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return new Response($this->renderView($provider->getView($data, $context), $form));
    }

    private function renderView(ComponentFormViewInterface $view, FormInterface $form): string
    {
        $formView = $form->createView();
        $formView->vars['attr'] = array_merge($formView->vars['attr'] ?? [], $view->getFormAttr());
        return $this->twig->render($view->getTemplate(), [
            'form' => $formView,
            'view' => $view
        ]);
    }


    private function addFlash(string $type, string $message): void
    {
        $session = $this->requestStack->getSession();

        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add($type, $message);
        }
    }
}
