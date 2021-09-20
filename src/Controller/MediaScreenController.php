<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MediaScreenController extends AbstractController
{
    /**
     * @Route("/media/screen", name="media_screen", options={"expose"=true})
     */
    public function getMediaScreen(
        Request $request
    ): Response
    {
        $mediaScreen = 'md';
        if (null !== $request->request->get('width')) {
            $width = filter_var($request->request->get('width'), FILTER_VALIDATE_INT);
            $mediaScreen = ($width > 800) ? 'md' : 'xs'; 
        }

        $request->getSession()->set('media_screen', $mediaScreen);
        return new Response();
    }
}
