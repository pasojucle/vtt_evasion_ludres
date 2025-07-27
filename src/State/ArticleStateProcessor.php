<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;

readonly class ArticleStateProcessor implements ProcessorInterface
{
    public function __construct(
        private PersistProcessor $persistProcessor,
        private EntityManagerInterface $entityManager,
    )
    {
        
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Article
    {
        $section = $data->getSection();
        if (!$section->getId()) {
            $this->entityManager->persist($section);
        }

        $chapter = $data->getChapter();
        if (!$chapter->getSection()) {
            $chapter->setSection($section);
        }
        dump($data);
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
