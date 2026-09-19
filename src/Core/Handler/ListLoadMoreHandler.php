<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\View\TurboStreamViewInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

readonly class ListLoadMoreHandler
{
    public function __construct(
        private Environment $twig,
        private RequestStack $requestStack,
    ) {
    }

    public function handle(

    ): Response {
        return new Response();
    }

    private function renderView(TurboStreamViewInterface $view): string
    {
        return '';
    }
}
