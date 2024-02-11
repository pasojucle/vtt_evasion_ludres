<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Cluster;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CacheService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getCache(): FilesystemAdapter
    {
        return new FilesystemAdapter($databaseName = $this->entityManager->getConnection()->getParams()['dbname']);
    }
    
    public function getCacheIndex(Cluster $entity): string
    {
        $reflectedClass = new ReflectionClass($entity);
        return sprintf('%s-%s', strtolower($reflectedClass->getShortName()), $entity->getId());
    }

    public function deleteCacheIndex(Cluster $entity): void
    {
        $cachePool = $this->getCache();
        $cachePool->deleteItem($this->getCacheIndex($entity));
    }

    public function prune(): bool
    {
        $cachePool = $this->getCache();
        return $cachePool->prune();
    }
}
