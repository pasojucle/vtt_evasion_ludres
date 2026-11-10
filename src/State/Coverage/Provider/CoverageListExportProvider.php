<?php

declare(strict_types=1);

namespace App\State\Coverage\Provider;

use App\Core\Contract\Provider\StreamListExportableInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\CoverageFilter;
use App\Mapper\LevelFilterMapper;
use App\Mapper\User\UserListExportMapper;
use App\Repository\MemberRepository;
use App\Service\SeasonService;
use App\State\Coverage\Trait\CoverageDataProviderTrait;

/**
 * @implements StreamListExportableInterface<CoverageFilter>
 */
class CoverageListExportProvider implements StreamListExportableInterface
{
    use FilterHydratorTrait;
    use CoverageDataProviderTrait;

    public function __construct(
        private MemberRepository $memberRepository,
        private UserListExportMapper $exportMapper,
        private SeasonService $seasonService,
        private LevelFilterMapper $levelFilterMapper,
    ) {
    }

    public function streamExportContent(AbstractFilter $filter, ?HandlerContext $context = null): void
    {
        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        $this->exportMapper->streamToCsv($entities);
    }

    public function filename(?HandlerContext $context = null): string
    {
        return 'export_inscriptions.csv';
    }
}
