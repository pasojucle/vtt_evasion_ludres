<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Cookie;
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