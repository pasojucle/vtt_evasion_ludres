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

  
        //$encKey = KeyFactory::generateEncryptionKey();
        //KeyFactory::save($encKey, '../data/encryption.key');

        //$encryptionKey = KeyFactory::loadEncryptionKey('../data/encryption.key');
        
        //dump($encryptionKey);

        return $this->render('setting/index.html.twig', [
            'controller_name' => 'SettingController',
        ]);
    }
}
