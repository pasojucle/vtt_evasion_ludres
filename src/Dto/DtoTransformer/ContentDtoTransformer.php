<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\ContentDto;
use App\Entity\Content;
use setasign\Fpdi\Fpdi;
use App\Service\ProjectDirService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\File\File;


class ContentDtoTransformer
{
    public function __construct(
        private ProjectDirService $projectDirService
    )
    {
        
    }

    public function fromEntity(Content $content): ContentDto
    {
        $backgroundDto = new ContentDto();
        if ($content) {
            $backgroundDto->id = $content->getId();
            $backgroundDto->title = $content->getTitle();
            $backgroundDto->route = $content->getRoute();
            $backgroundDto->isFlash = $content->IsFlash();
            $backgroundDto->content = $content->getContent();
            $backgroundDto->fileName = $this->getPath($content->getFileName());
            $backgroundDto->fileTag = $this->getFileTag($backgroundDto->fileName);
            $backgroundDto->fileRatio = $this->getFileRatio($backgroundDto->fileName, $backgroundDto->fileTag);

            $backgroundDto->buttonLabel = $content->getButtonLabel() ?? 'Voir';
            $backgroundDto->url = $content->getUrl();

            $backgroundDto->contentStyleMd = $this->getContentStyleMd(null !== $backgroundDto->fileName, null !== $backgroundDto->url);
        }

        return $backgroundDto;
    }

        
    public function fromEntities(Paginator|Collection|array $contentEntities): array
    {
        $contents = [];
        foreach($contentEntities as $contentEntity) {
            $contents[] = $this->fromEntity($contentEntity);
        }

        return $contents;
    }

    public function FromHomeContents(Paginator|Collection|array $contentEntities): array
    {
        $contents = [];
        foreach($contentEntities as $contentEntity) {
            $type = ($contentEntity->isFlash()) ? 'flashes' : 'contents';
            $contents[$type][] = $this->fromEntity($contentEntity);
        }

        return $contents;
    }

    private function getPath(?string $fileName): ?string
    {
        return ($fileName) ? $this->projectDirService->path('upload', $fileName) : null;
    }

    private function getFileTag(?string $fileName): ?string
    {
        if ($fileName) {
            $file = new File($fileName);

            return (str_contains($file->getMimeType(), 'image')) ? 'img' : 'pdf';
        }

        return null;
    }

    private function getFileRatio(?string $fileName, ?string $fileTag): ?float
    {
        if ($fileName && is_file($fileName) && null !== $fileTag) {
            list($width, $height) = ('pdf' === $fileTag) ? $this->getPdfSize($fileName) : $this->getImageSize($fileName);

            return $height / $width;
        }

        return null;
    }

    private function getImageSize(?string $fileName): array
    {
        return getimagesize($fileName);
    }

    private function getPdfSize(?string $fileName): array
    {
        $pdf = new Fpdi();
        $pdf->setSourceFile($fileName);

        return [$pdf->GetPageWidth(), $pdf->GetPageHeight()];
    }

    private function getContentStyleMd(bool $hasFileName, bool $hasUrl): string
    {
        $col = 12;
        if ($hasFileName) {
            $col -= 3;
        }
        if ($hasUrl) {
            $col -= 3;
        }

        return 'col-md-' . $col;
    }
}