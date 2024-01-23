<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Cluster;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;

class CacheService
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function getCache(): FilesystemAdapter
    {
        return new FilesystemAdapter($this->requestStack->getSession()->get('databaseName'));
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
