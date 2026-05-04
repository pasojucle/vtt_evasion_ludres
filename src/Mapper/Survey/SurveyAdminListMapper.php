<?php

declare(strict_types=1);

namespace App\Mapper\Survey;

use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\DropdownItemDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\Filter\SurveyFilter;
use App\Dto\ListCellItemDto;
use App\Dto\ListDto;
use App\Dto\ListItemDto;
use App\Entity\Survey;
use App\Mapper\PaginatorMapper;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SurveyAdminListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private PaginatorMapper $paginatorMapper,
        private SurveyAdminDropdownMapper $surveyAdminDropdownMapper
    ) 
    {

    }

    public function mapToView(Paginator $entities, string $route, int $currentPage, SurveyFilter $filter): ListDto
    {
        $items = [];
        foreach ($entities as $entity) {
            $status = $entity->getStatus();
            $items[] = new ListItemDto(
                cells: [
                    new ListCellItemDto($entity->getTitle()),
                    new ListCellItemDto($status->trans($this->translator), ListCellItemDto::TYPE_BADGE, $status->variant()),
                    new ListCellItemDto((string) $entity->getRespondents()->count(), ListCellItemDto::TYPE_BADGE),
                ],
                dropdown: $this->surveyAdminDropdownMapper->mapToView($entity),
                url: $this->urlGenerator->generate($entity->isAnonymous() ? 'admin_anonymous_survey' : 'admin_survey', [
                    'survey' => $entity->getId()
                ]),
            );
        }
      
        return new ListDto(
            items: $items,
            paginator: $this->paginatorMapper->fromEntities($entities, $route, $currentPage, $filter),
            addItem: new ButtonDto(
                label: 'Ajouter un sondage',
                url: $this->urlGenerator->generate('admin_survey_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
        );
    }
}