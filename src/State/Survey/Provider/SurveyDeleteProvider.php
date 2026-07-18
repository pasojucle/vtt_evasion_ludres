<?php

declare(strict_types=1);

namespace App\State\Survey\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Survey;
use App\Mapper\DestructiveModalMapper;
use App\State\Interface\FormComponentProviderInterface;

class SurveyDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }

    /**
     * @param Survey $entity
     */
    public function mapToView(object $entity, ?string $fallback = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(
            sprintf('<p>Toutes les données relative à ce vote seront supprimées.</p><p>Etes-vous certain de supprimer le sondage <b>%s</b> ?</p><p>Toutes les données relatives au sondages seront supprimées.</p><p>Cette opération est irréversible.</p>', $entity->getTitle()),
        );
    }
}
