<?php

declare(strict_types=1);

namespace App\State\Skill\Provider;

use App\Core\Contract\Provider\StreamListExportableInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\SkillFilter;
use App\Mapper\Skill\SkillListExportMapper;
use App\Repository\SkillRepository;
use App\State\Skill\Trait\SkillDataProviderTrait;

/**
 * @implements StreamListExportableInterface<SkillFilter>
 */
class SkillExportProvider implements StreamListExportableInterface
{
    use FilterHydratorTrait;
    use SkillDataProviderTrait;

    public function __construct(
        private SkillRepository $skillRepository,
        private SkillListExportMapper $skillListExportMapper,
    ) {
    }

    public function streamExportContent(?AbstractFilter $filter, ?HandlerContext $context = null): void
    {
        $skills = $this->getQueryBuilder($filter)->getQuery()->getResult();
        $this->skillListExportMapper->streamToCsv($skills);
    }

    public function basename(?HandlerContext $context = null): string
    {
        return 'liste_des_compétences.csv';
    }
}
