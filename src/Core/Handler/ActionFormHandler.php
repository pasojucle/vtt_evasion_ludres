<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Processor\ProcessorInterface;
use App\Core\Contract\Provider\DataInitializerInterface;
use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\InputInitializerInterface;
use App\Core\Contract\Provider\StreamDownloadProviderInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Contract\Provider\TurboStreamUpdateProviderInterface;
use App\Core\Contract\View\ComponentFormViewInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Core\Dto\StreamDownloadProcessorResult;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Core\Http\StreamedDownloadResponseFactory;
use Symfony\Component\Form\ClearableErrorsInterface;
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
        private StreamedDownloadResponseFactory $streamedDownloadResponseFactory,
    ) {
    }

    public function handle(
        Request $request,
        object $data,
        FormComponentProviderInterface $provider,
        ProcessorInterface $processor,
        string $formClass = FormType::class,
        ?object $parent = null,
    ): Response {
        $context = HandlerContext::fromRequest($request, $parent);
        $formData = $data;

        if ($provider instanceof DataInitializerInterface) {
            $formData = $provider->initializeData($data);
        }

        if ($provider instanceof InputInitializerInterface) {
            $provider->setDefaultValues($formData);
        }

        $form = $this->formFactory->create($formClass, $formData, array_merge([
            'action' => $request->getUri(),
        ], $provider->getFormOptions($formData, $context)));

        $form->handleRequest($request);
        if (!$request->isMethod('POST') || !$form->isSubmitted()) {
            return $this->renderFormView($provider, $formData, $context, $form);
        }

        if ($this->isFilterOrAjaxRequest($request)) {
            if ($form instanceof ClearableErrorsInterface) {
                $form->clearErrors(true);
            }

            return $this->renderFormView($provider, $formData, $context, $form);
        }

        if ($form->isValid()) {
            return $this->handleValidSubmission($request, $form, $provider, $processor, $context);
        }

        return $this->renderInvalidSubmission($provider, $formData, $context, $form);
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

    private function isFilterOrAjaxRequest(Request $request): bool
    {
        return $request->headers->has('X-Form-Filter') || $request->isXmlHttpRequest();
    }

    private function renderFormView(
        FormComponentProviderInterface $provider,
        object $formData,
        HandlerContext $context,
        FormInterface $form,
    ): Response {
        $view = $provider->getView($formData, $context);
        return new Response($this->renderView($view, $form));
    }

    private function handleValidSubmission(
        Request $request,
        FormInterface $form,
        FormComponentProviderInterface $provider,
        ProcessorInterface $processor,
        HandlerContext $context,
    ): Response {
        $result = $processor->process(
            ActionPayload::create($form, $request),
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

        if ($result instanceof StreamDownloadProcessorResult && $provider instanceof StreamDownloadProviderInterface) {
            $data = $result->data;
            return $this->streamedDownloadResponseFactory->create(
                $data,
                $provider,
                $provider->basename($data),
                $provider->contentType(),
            );
        }

        throw new \LogicException(sprintf(
            'Résultat de traitement non géré (%s) ou provider incompatible.',
            get_debug_type($result),
        ));
    }

    private function renderInvalidSubmission(
        FormComponentProviderInterface $provider,
        object $formData,
        HandlerContext $context,
        FormInterface $form,
    ): Response {
        if ($provider instanceof TurboStreamUpdateProviderInterface) {
            $streamView = $provider->getUpdateStreamView($formData, $context);
            return new Response(
                $this->twig->render($streamView->getStreamTemplate(), [
                        'view' => $streamView,
                        'form' => $form->createView(),
                    ]),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                ['Content-Type' => 'text/vnd.turbo-stream.html'],
            );
        }

        return new Response(
            $this->renderView($provider->getView($formData, $context), $form),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
