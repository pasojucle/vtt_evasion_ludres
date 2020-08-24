<?php

namespace App\Service;

use Throwable;
use App\Entity\Article;
use Psr\Log\LoggerInterface;
use ParagonIE\Halite\KeyFactory;
use App\Service\ParameterService;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\Halite\Symmetric\Crypto as Symmetric;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class EncryptionService
{

    private $articleRepository;
    private $entityManager;
    private $logger;
    private $session;
    private $params;
 
    public function __construct(
        ParameterBagInterface $params,
        ParameterService $parameterService,
        ArticleRepository $articleRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        SessionInterface $session
    )
    {
        $this->params = $params;
        $this->parameterEncryption = $parameterService->getParameter('ENCRYPTION');
        $this->articleRepository = $articleRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->session = $session;
    }

    public function loadKey(): EncryptionKey
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

    public function encryptFields(Article $article)
    {
        $key = $this->loadKey();

        // Encrypt the variables
        $content = $this->encrypt('content', $article->getContent(), $key);

        // Set the entity variables
        $article->setContent($content);

        return $article;
    }

    public function encrypt($fieldName, $value, $key)
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

    public function decryptFields(Article $article)
    {
        $key = $this->loadKey();
        $id = $article->getId();

        // Decrypt the variables
        $content = $this->decrypt($id, 'content', $article->getContent(), $key);

        // Set the entity variables
        if (null != $content) {
            $article->setContent($content);
        }
    }

    public function decrypt($id, $fieldName, $value, $key)
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
 
    public function toggleEncryption($parameterEncryption) {
        $articles = $this->articleRepository->findAll();
        if (!empty($articles)) {
            foreach($articles as $article) {
                $article->setEncryptionLock(true);
                if ($parameterEncryption) {
                    $this->encryptFields($article);
                } 
                
                $this->entityManager->persist($article);
            }
        }

        $this->entityManager->flush();
        //$encKey = KeyFactory::generateEncryptionKey();
        //KeyFactory::save($encKey, '../data/encryption.key');

        //$encryptionKey = KeyFactory::loadEncryptionKey('../data/encryption.key');
        
        //dump($encryptionKey);
    }


}