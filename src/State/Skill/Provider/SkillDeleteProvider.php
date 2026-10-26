<?php

declare(strict_types=1);

namespace App\State\Skill\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\DialogModalView;
use App\Entity\Skill;
use App\Mapper\DestructiveModalMapper;

class SkillDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        /** @var Skill $data */
        return $this->destructiveModalMapper->mapToView(sprintf(
            'Etes vous certain de supprimer la compétence <b>%s</b> ?',
            strip_tags($data->getContent())
        ));
    }

    public function getFormOptions(object $data, ?HandlerContext $content = null): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->modal#handleFormSubmit',
            ],
        ];
    }
}
