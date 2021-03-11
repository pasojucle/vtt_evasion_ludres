<?php

namespace App\EventListeners;

use App\Entity\Article;
use App\Service\ParameterService;
use App\Service\EncryptionService;
use Doctrine\Persistence\Event\LifecycleEventArgs;


class EntityListener
{
    private ParameterService $parameterService;
    private EncryptionService $encryptionService;
    private $parameterEncryption;

    public function __construct(
        ParameterService $parameterService,
        EncryptionService $encryptionService
    )
    {
        $this->parameterService = $parameterService;
        $this->parameterEncryption = $this->parameterService->getParameter('ENCRYPTION');
        $this->encryptionService = $encryptionService;
    }

    public function prePersist(Article $article, LifecycleEventArgs $event)
    {
        dump($article);
        if ($this->parameterEncryption && false === $article->getEncryptionLock())
        {
            $this->encryptionService->encryptFields($article);
        }
    }

    public function preUpdate(Article $article, LifecycleEventArgs $event)
    {
        dump($article);
        if ($this->parameterEncryption && false === $article->getEncryptionLock())
        {
            $this->encryptionService->encryptFields($article);
        }
    }

    public function postLoad(Article $article, LifecycleEventArgs $event)
    {
        dump($article);

        if ($this->parameterEncryption)
        {
            $this->encryptionService->decryptFields($article);
        }
    }
}