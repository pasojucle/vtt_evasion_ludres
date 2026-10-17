<?php

declare(strict_types=1);

namespace App\State\Survey\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\InputInitializerInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\SheetFormWrapperView;
use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Mapper\Survey\SurveyUpdateMapper;
use DateInterval;
use DateTimeImmutable;

/**
 * @implements FormComponentProviderInterface<Survey>
 */
class SurveyUpdateProvider implements FormComponentProviderInterface, InputInitializerInterface
{
    public function __construct(
        private SurveyUpdateMapper $mapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): SheetFormWrapperView
    {
        return $this->mapper->mapToView(
            $data,
            ($data->getId()) ? 'modifier' : 'ajouter',
            $context->tab,
        );
    }

    public function setDefaultValues(object $data): object
    {
        if (!$data->getId()) {
            $today = new DateTimeImmutable();
            $data->setStartAt($today);
            $data->setEndAt($today->add(new DateInterval('P7D')));
            $issue = new SurveyIssue();
            $data->addSurveyIssue($issue);
        }

        return $data;
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'issues_disabled' => !$data->getRespondents()->isEmpty(),
            'attr' => [
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
                'data-controller' => 'form-modifier',
            ]
        ];
    }
}
