<?php

declare(strict_types=1);

namespace App\ViewModel\Documentation;

use App\ViewModel\ServicesPresenter;
use Doctrine\Common\Collections\Collection;

class DocumentationsViewModel
{
    public ?array $documentations = [];

    public static function fromDocumentations(array|Collection $documentations, ServicesPresenter $services): DocumentationsViewModel
    {
        $documentationsViewModel = [];
        if (!empty($documentations)) {
            foreach ($documentations as $documentation) {
                $documentationView = DocumentationViewModel::fromDocumentation($documentation, $services);
                $documentationsViewModel[] = $documentationView;
            }
        }

        $documentationsView = new self();
        $documentationsView->documentations = $documentationsViewModel;
        return $documentationsView;
    }
}
