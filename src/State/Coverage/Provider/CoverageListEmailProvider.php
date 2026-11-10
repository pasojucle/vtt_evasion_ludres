<?php

declare(strict_types=1);

namespace App\State\Coverage\Provider;

use App\Core\Contract\Provider\ListJsonProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\CoverageFilter;
use App\Mapper\EmailClipboardMapper;
use App\Mapper\LevelFilterMapper;
use App\Repository\MemberRepository;
use App\Service\SeasonService;
use App\State\Coverage\Trait\CoverageDataProviderTrait;

/**
 * @implements ListJsonProviderInterface<CoverageFilter>
 */
class CoverageListEmailProvider implements ListJsonProviderInterface
{
    use FilterHydratorTrait;
    use CoverageDataProviderTrait;

    public function __construct(
        private MemberRepository $memberRepository,
        private LevelFilterMapper $levelFilterMapper,
        private EmailClipboardMapper $emailClipboardMapper,
        private SeasonService $seasonService,
    ) {
    }

    public function getJson(AbstractFilter $filter, HandlerContext|null $context = null): array
    {
        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        return ['emails' => $this->emailClipboardMapper->mapToEmailCsvString($entities)];
    }
}
