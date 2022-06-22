<?php

declare(strict_types=1);

namespace App\ViewModel\Content;

use App\Entity\Content;
use App\ViewModel\AbstractViewModel;
use App\ViewModel\ServicesPresenter;
use setasign\Fpdi\Fpdi;
use Symfony\Component\HttpFoundation\File\File;

class ContentViewModel extends AbstractViewModel
{
    public ?Content $entity;

    public ?string $title;

    public ?string $route;

    public ?string $content;

    public ?bool $isFlash;

    public ?string $fileName;

    public ?string $fileTag;

    public ?float $fileRatio;

    public ?string $buttonLabel;

    public ?string $url;

    public ?string $contentStyleMd;

    private ?ServicesPresenter $services;

    public static function fromContent(Content $content, ServicesPresenter $services)
    {
        $contentView = new self();
        $contentView->entity = $content;
        $contentView->services = $services;
        $contentView->title = $content->getTitle();
        $contentView->route = $content->getRoute();
        $contentView->content = $content->getContent();
        $contentView->fileName = $contentView->getFileName();
        $contentView->fileTag = $contentView->getFileTag();
        $contentView->fileRatio = $contentView->getFileRatio();

        $contentView->buttonLabel = $content->getButtonLabel() ?? 'Voir';
        $contentView->url = $content->getUrl();

        $contentView->contentStyleMd = $contentView->getContentStyleMd();
dump($contentView);
        return $contentView;
    }

    private function getContentStyleMd(): string
    {
        $col = 12;
        if (null !== $this->fileName) {
            $col -= 3;
        }
        if ($this->url) {
            $col -= 3;
        }

        return 'col-md-'.$col;
    }

    private function getFileName(): ?string
    {

        return ($this->entity->getFileName()) ? $this->services->uploadsDirectory.$this->entity->getFileName() : null;
    }

    private function getFileTag(): ?string
    {
        if ($this->entity->getFileName()) {
            $file = new File($this->services->uploadsDirectoryPath.$this->entity->getFileName());

            return (str_contains($file->getMimeType(), 'image')) ? 'img' : 'pdf';
        }

        return null;
    }

    private function getFileRatio(): ?float
    {
        if ($this->entity->getFileName() && is_file($this->services->uploadsDirectoryPath.$this->entity->getFileName())) {
            list($width, $height) = match ($this->fileTag) {
                'pdf' => $this->getPdfSize(),
                'img' => $this->getImageSize()
            };

            return $height / $width;
        }

        return null;
    }

    private function getImageSize(): array
    {
        return getimagesize($this->services->uploadsDirectoryPath.$this->entity->getFileName());
    }

    private function getPdfSize(): array
    {
        $pdf = new Fpdi();
        $pdf->setSourceFile($this->services->uploadsDirectoryPath.$this->entity->getFileName());

        return [$pdf->GetPageWidth(), $pdf->GetPageHeight()];
    }
}
