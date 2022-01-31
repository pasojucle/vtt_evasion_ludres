<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class MediaScreenService
{
    private RequestStack $request;

    public function __construct(RequestStack $request)
    {
        $this->request = $request;
    }

    public function getMediaScreen(): string
    {
        $mediaScreen = $this->request->getCurrentRequest()->cookies->get('media_screen');

        return ($mediaScreen) ? $mediaScreen : 'md';
    }
}
