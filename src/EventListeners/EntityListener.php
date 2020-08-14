<?php

namespace App\EventListeners;

use App\Entity\Article;
use Psr\Log\LoggerInterface;
use ParagonIE\Halite\KeyFactory;
use App\Service\ParameterService;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\Halite\Symmetric\Crypto as Symmetric;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class EntityListener
{
    private $params;
    private $session;
    private $logger;
    private $parameterEncryption;

    public function __construct(ParameterBagInterface $params, SessionInterface $session, LoggerInterface $logger, ParameterService $parameterService)
    {
        $this->params = $params;
        $this->session = $session;
        $this->logger = $logger;
        $this->parameterEncryption = $parameterService->getParameter('ENCRYPTION');
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Article)
        {
            $this->encryptFields($entity);
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Article && $this->parameterEncryption)
        {
            $this->encryptFields($entity);
        }
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Article && $this->parameterEncryption)
        {
            $this->decryptFields($entity);
        }
    }

    private function loadKey(): EncryptionKey
    {
        try
        {
            return KeyFactory::loadEncryptionKey($this->params->get('encryption_key'));
        
        }
        catch(\Throwable $e)
        {
            $this->session->getFlashBag()->add('danger', 'Unable to load encryption key!');
            $this->logger->emergency(
                'Unable to lod the encryption key!', array(
                'error' => $e->getMessage(),
            ));
            throw $e;
        }
    }

    private function encryptFields(Article $article)
    {
        $key = $this->loadKey();

        // Encrypt the variables
        $content = $this->encrypt('Content', $article->getContent(), $key);
        
        // Set the entity variables
        $article->setContent($content);

        return $article;
    }

    private function encrypt($fieldName, $value, $key)
    {
        try {
            return Symmetric::encrypt(
                new HiddenString($value),
                $key
            );
        } catch(\Throwable $e)
        {
            $this->session->getFlashBag()->add('danger', 'Unable to encrypt field');
            $this->logger->critical(
                'Unable to encrypt field "'.$fieldName.'" in Article entity. DB update terminated.', array(
                'error' => $e->getMessage(),
            ));
            throw $e;
        }
    }

    private function decryptFields(Article $article)
    {
        $key = $this->loadKey();
        $id = $article->getId();

        // Decrypt the variables
        $content = $this->decrypt($id, 'Content', $article->getContent(), $key);

        // Set the entity variables
        if (null != $content) {
            $article->setContent($content);
        }
    }

    private function decrypt($id, $fieldName, $value, $key)
    {
        try
        {
            return Symmetric::decrypt($value, $key);
        }
        catch (\Throwable $e)
        {
            $this->session->getFlashBag()->add('warning', 'Unable to decrypt field');
            $this->logger->warning(
                'Unable to decrypt field "'.$fieldName.'" in Article entity for ID: '.$id, array(
                'error' => $e->getMessage(),
            ));
        }
    }
}