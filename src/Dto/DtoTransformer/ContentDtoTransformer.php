<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\ContentDto;
use App\Dto\ContentsDto;
use App\Entity\Content;
use App\Service\ProjectDirService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use setasign\Fpdi\Fpdi;
use Symfony\Component\HttpFoundation\File\File;

class ContentDtoTransformer
{
    public function __construct(
        private ProjectDirService $projectDirService
    ) {
    }

    public function fromEntity(?Content $content): ContentDto
    {
        $contentDto = new ContentDto();
        if ($content) {
            $contentDto->id = $content->getId();
            $contentDto->title = $content->getTitle();
            $contentDto->route = $content->getRoute();
            $contentDto->isFlash = $content->IsFlash();
            $contentDto->content = $content->getContent();
            $contentDto->fileName = $this->getPath($content->getFileName());
            $contentDto->fileTag = $this->getFileTag($contentDto->fileName);
            $contentDto->fileRatio = $this->getFileRatio($contentDto->fileName, $contentDto->fileTag);

            $contentDto->buttonLabel = $content->getButtonLabel() ?? 'Voir';
            $contentDto->url = $content->getUrl();

            $contentDto->contentStyleMd = $this->getContentStyleMd(null !== $contentDto->fileName, null !== $contentDto->url);
        }

        return $contentDto;
    }

        
    public function fromEntities(Paginator|Collection|array $contentEntities): ContentsDto
    {
        $contentsDto = new ContentsDto();
        foreach ($contentEntities as $contentEntity) {
            $content = $this->fromEntity($contentEntity);
            $contentsDto->contents[] = $content;
            $type = ($content->isFlash) ? 'flashes' : 'contents';
            $contentsDto->homeContents[$type][] = $content;
        }

        return $contentsDto;
    }

    public function FromHomeContents(Paginator|Collection|array $contentEntities): array
    {
        $contents = [];
        foreach ($contentEntities as $contentEntity) {
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
