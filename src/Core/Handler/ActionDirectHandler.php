<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Contract\View\TurboStreamViewInterface;
use App\Core\Dto\ActionDirectPayload;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Twig\Environment;

readonly class ActionDirectHandler
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public function handle(
        Request $request,
        object $data,
        TurboStreamProviderInterface $provider,
        TurboStreamProcessorInterface $processor,
    ): Response {
        $result = $processor->process(new ActionDirectPayload(
            data: $data,
            token: $request->query->get('csrfToken')
        ));
        $this->addFlash($request, $result->flashType, $result->messageKey);

        $streamView = $provider->getStreamView($result->data);
        return new Response(
            $this->renderView($streamView),
            Response::HTTP_OK,
            ['Content-Type' => 'text/vnd.turbo-stream.html', ]
        );
    }

    private function renderView(TurboStreamViewInterface $view): string
    {
        return $this->twig->render($view->getStreamTemplate(), [
            'view' => $view
        ]);
    }

    private function addFlash(Request $request, string $type, string $message): void
    {
        $session = $request->getSession();

        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add($type, $message);
        }
    }
}
