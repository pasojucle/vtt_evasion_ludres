<?php

namespace App\EventListeners;

use App\Entity\Article;
use App\Service\ParameterService;
use App\Service\EncryptionService;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EntityListener
{
    private ParameterService $parameterService;
    private EncryptionService $encryptionService;
    private SessionInterface $session;
    private $parameterEncryption;

    public function __construct(
        ParameterService $parameterService,
        EncryptionService $encryptionService,
        SessionInterface $session
    )
    {
        $this->parameterService = $parameterService;
        $this->parameterEncryption = $this->parameterService->getParameter('ENCRYPTION');
        $this->encryptionService = $encryptionService;
        $this->session = $session;
    }

    public function prePersist(Article $article, LifecycleEventArgs $event)
    {
        if ($this->parameterEncryption && true !== $this->session->get('encryptionLock'))
        {
            $this->encryptionService->encryptFields($article);
        }
    }

    public function preUpdate(Article $article, LifecycleEventArgs $event)
    {
        if ($this->parameterEncryption && true !== $this->session->get('encryptionLock'))
        {
            $this->encryptionService->encryptFields($article);
        }
    }

    public function postLoad(Article $article, LifecycleEventArgs $event)
    {
        if ($this->parameterEncryption && true !== $this->session->get('encryptionLock'))
        {
            $this->encryptionService->decryptFields($article);
        }
    }
}