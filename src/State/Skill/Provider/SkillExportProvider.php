<?php

declare(strict_types=1);

namespace App\State\Skill\Provider;

use App\Core\Contract\Provider\ListFilteredProviderInterface;
use App\Core\Contract\Provider\StreamActionExportableInterface;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\SkillFilter;
use App\Entity\Skill;
use App\Mapper\Skill\SkillListExportMapper;
use App\Repository\SkillRepository;
use App\State\Skill\Trait\SkillDataProviderTrait;

/**
 * @implements StreamActionExportableInterface<Skill>
 */
class SkillExportProvider implements StreamActionExportableInterface, ListFilteredProviderInterface
{
    use FilterHydratorTrait;
    use SkillDataProviderTrait;

    public function __construct(
        private SkillRepository $skillRepository,
        private SkillListExportMapper $skillListExportMapper,
    ) {
    }

    /**
     * @param SkillFilter $filter
     */
    public function streamExportContent(?object $data, ?AbstractFilter $filter): void
    {
        $skills = $this->getQueryBuilder($filter)->getQuery()->getResult();
        $this->skillListExportMapper->streamToCsv($skills);
    }

    public function basename(?object $data): string
    {
        return 'liste_des_compétences.csv';
    }
}
