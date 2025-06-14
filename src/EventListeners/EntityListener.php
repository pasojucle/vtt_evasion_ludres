<?php

namespace App\EventListeners;

use App\Entity\Article;
use App\Entity\Chapter;
use App\Service\EncryptionService;
use App\Service\ParameterService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Article::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Article::class)]
#[AsEntityListener(event: Events::postLoad, method: 'postLoad', entity: Article::class)]
class EntityListener
{
    public function __construct(
        private readonly ParameterService $parameterService,
        private readonly EncryptionService $encryptionService,
        private readonly RequestStack $request,
    ) {
    }

    public function prePersist(Article $article)
    {
        if ($this->isEncrypted()) {
            $this->encryptionService->encryptFields($article);
        }
    }

    public function preUpdate(Article $article)
    {
        if ($this->isEncrypted()) {
            $this->encryptionService->encryptFields($article);
        }
    }

    public function postLoad(Article $article)
    {
        if ($this->isEncrypted()) {
            $this->encryptionService->decryptFields($article);
        }
    }

    private function isEncrypted(): bool
    {
        $session = $this->request->getSession();
        $parameterEncryption = $this->parameterService->getParameter('ENCRYPTION');
        return $parameterEncryption && true !== $session->get('encryptionLock');
    }
}
