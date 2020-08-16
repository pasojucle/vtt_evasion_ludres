<?php

namespace App\EventListeners;

use App\Entity\Article;
use App\Service\ParameterService;
use App\Service\EncryptionService;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;



class EntityListener
{
    private $parameterEncryption;
    private $encryptionService;

    public function __construct(
        ParameterService $parameterService,
        EncryptionService $encryptionService
    )
    {
        $this->parameterEncryption = $parameterService->getParameter('ENCRYPTION');
        $this->encryptionService = $encryptionService;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Article && $this->parameterEncryption && null === $entity->getEncryption())
        {
            $this->encryptionService->encryptFields($entity);
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        
        if ($entity instanceof Article && $this->parameterEncryption && null === $entity->getEncryption())
        {
            $this->encryptionService->encryptFields($entity);
        }
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Article && $this->parameterEncryption)
        {
            $this->encryptionService->decryptFields($entity);
        }
    }
}