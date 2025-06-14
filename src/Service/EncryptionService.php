<?php

namespace App\Service;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Service\ParameterService;
use Doctrine\ORM\EntityManagerInterface;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto as Symmetric;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Throwable;

class EncryptionService
{
    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly ArticleRepository $articleRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly RequestStack $request,
    ) {
    }

    public function loadKey(): EncryptionKey
    {
        try {
            return KeyFactory::loadEncryptionKey($this->params->get('encryption_key'));
        } catch (\Throwable $e) {
            /** @var FlashBagAwareSessionInterface $session */
            $session = $this->request->getSession();
            $session->getFlashBag()->add('danger', 'Unable to load encryption key!');
            $this->logger->emergency(
                'Unable to lod the encryption key!',
                [
                'error' => $e->getMessage(),
            ]
            );
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
        } catch (\Throwable $e) {
            /** @var FlashBagAwareSessionInterface $session */
            $session = $this->request->getSession();
            $session->getFlashBag()->add('danger', 'Unable to encrypt field');
            $this->logger->critical(
                'Unable to encrypt field "' . $fieldName . '" in Article entity. DB update terminated.',
                [
                'error' => $e->getMessage(),
            ]
            );
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
            $article->setContent($content->getString());
        }
    }

    public function decrypt($id, $fieldName, $value, $key)
    {
        try {
            return Symmetric::decrypt($value, $key);
        } catch (\Throwable $e) {
            /** @var FlashBagAwareSessionInterface $session */
            $session = $this->request->getSession();
            $session->getFlashBag()->add('warning', 'Unable to decrypt field');
            $this->logger->warning(
                'Unable to decrypt field "' . $fieldName . '" in Article entity for ID: ' . $id,
                [
                'error' => $e->getMessage(),
            ]
            );
        }
    }
 
    public function toggleEncryption($parameterEncryption)
    {
        $articles = $this->articleRepository->findAll();
        $session = $this->request->getSession();
        $session->set('encryptionLock', true);
        if (!empty($articles)) {
            foreach ($articles as $article) {
                if ($parameterEncryption) {
                    $this->encryptFields($article);
                } else {
                    $this->decryptFields($article);
                }
                
                $this->entityManager->persist($article);
            }
        }

        $this->entityManager->flush();
    }
}
