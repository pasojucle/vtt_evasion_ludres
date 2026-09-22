<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Contract\View\TurboStreamViewInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\FlashMessage;
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
        $flash = $result->messageKey ? new FlashMessage($result->flashType, $result->messageKey) : null;

        $streamView = $provider->getStreamView(data: $result->data, flashMessage: $flash);
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
}
