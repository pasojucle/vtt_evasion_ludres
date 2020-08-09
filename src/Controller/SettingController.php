<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SettingController extends AbstractController
{
    /**
     * @Route("/settings", name="settings")
     */
    public function settings()
    {
        return $this->render('setting/index.html.twig', [
            'controller_name' => 'SettingController',
        ]);
    }
}
