<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\DocumentationDto;
use App\Entity\Documentation;
use App\Service\ProjectDirService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DocumentationDtoTransformer
{
    public function __construct(
        private ProjectDirService $projectDirService,
        private UrlGeneratorInterface $router,
    ) {
    }

    public function fromEntity(Documentation $documentation): DocumentationDto
    {
        $fileName = $documentation->getFilename();
        $filePath = ($documentation->getFilename()) ? $this->projectDirService->path('documentation', $fileName) : null;

        $documentationDto = new DocumentationDto();
        $documentationDto->name = $documentation->getName();
        $documentationDto->filename = $fileName;
        $documentationDto->source = $this->getSource($filePath);
        $documentationDto->mimeType = $this->getMimeType($filePath);


        return $documentationDto;
    }

    public function fromEntities(Paginator|Collection|array $documentationEntities): array
    {
        $documentations = [];
        foreach ($documentationEntities as $documentationEntity) {
            $documentations[] = $this->fromEntity($documentationEntity);
        }

        return $documentations;
    }

    private function getSource(?string $filePath): ?string
    {
        return ($filePath)
        ? $this->router->generate('get_file', ['filename' => base64_encode($filePath)])
        : null;
    }

    private function getMimeType(?string $filePath): ?string
    {
        if (file_exists($filePath)) {
            return mime_content_type($filePath);
        }

        return null;
    }
}
