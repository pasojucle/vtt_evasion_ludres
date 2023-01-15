<?php

declare(strict_types=1);

namespace App\ViewModel\Documentation;

use App\Entity\Documentation;
use App\ViewModel\AbstractViewModel;
use App\ViewModel\ServicesPresenter;
use setasign\Fpdi\Fpdi;
use Symfony\Component\HttpFoundation\File\File;

class DocumentationViewModel extends AbstractViewModel
{
    public ?Documentation $entity;

    public ?string $name;

    public ?string $filename;

    public ?string $source;

    public ?string $mimeType;

    private ServicesPresenter $services;


    public static function fromDocumentation(Documentation $documentation, ServicesPresenter $services)
    {
        $documentationView = new self();
        $documentationView->entity = $documentation;
        $documentationView->services = $services;
        $documentationView->name = $documentation->getName();
        $documentationView->filename = $documentation->getFilename();
        $documentationView->source = $documentationView->getSource();
        $documentationView->mimeType = $documentationView->getMimeType();


        return $documentationView;
    }

    private function getSource(): ?string
    {
        return ($this->entity->getFilename())
        ? $this->services->router->generate('get_file', ['filename' => base64_encode($this->services->documentationDirectoryPath . $this->entity->getFilename())])
        : null;
    }

    private function getMimeType(): ?string
    {
        if (file_exists($this->services->documentationDirectoryPath . $this->entity->getFilename())) {
            return mime_content_type($this->services->documentationDirectoryPath . $this->entity->getFilename());
        }

        return null;
    }
}
