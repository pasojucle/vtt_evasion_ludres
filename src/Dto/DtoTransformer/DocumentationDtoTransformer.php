<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\DocumentationDto;
use App\Entity\Documentation;
use App\Service\ProjectDirService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use ReflectionProperty;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DocumentationDtoTransformer
{
    public function __construct(
        private ProjectDirService $projectDirService,
        private UrlGeneratorInterface $router,
    ) {
    }

    public function fromEntity(?Documentation $documentation, bool $novelty = false): DocumentationDto
    {
        $fileName = $documentation?->getFilename();
        $filePath = ($documentation?->getFilename()) ? $this->projectDirService->path('documentation', $fileName) : null;

        $documentationDto = new DocumentationDto();
        if ((new ReflectionProperty($documentation, 'id'))->isInitialized($documentation)) {
            $documentationDto->id = $documentation->getId();
            $documentationDto->name = $documentation->getName();
            $documentationDto->filename = $fileName;
            $documentationDto->source = $this->getSource($filePath);
            $documentationDto->mimeType = $this->getMimeType($filePath);
            $documentationDto->link = $this->getLink($documentation);
            $documentationDto->novelty = $novelty;
        }

        return $documentationDto;
    }

    public function fromEntities(Paginator|Collection|array $documentationEntities, array $linkViewedIds = []): array
    {
        $documentations = [];
        foreach ($documentationEntities as $documentationEntity) {
            $documentations[] = $this->fromEntity($documentationEntity, in_array($documentationEntity->getId(), $linkViewedIds));
        }

        return $documentations;
    }

    private function getSource(?string $filePath): ?string
    {
        return ($filePath)
        ? $this->router->generate('get_file', ['filename' => base64_encode($filePath)])
        : null;
    }

    private function getLink(?Documentation $documentation): ?string
    {
        return ($documentation->getLink())
        ? $this->router->generate('notification_outside_link', ['documentation' => $documentation->getId()])
        : null;
    }

    private function getMimeType(?string $filePath): ?string
    {
        if ($filePath && file_exists($filePath)) {
            return mime_content_type($filePath);
        }

        return null;
    }
}
