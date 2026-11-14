<?php

declare(strict_types=1);

namespace App\State\Registration\Provider;

use App\Core\Contract\Provider\StreamListExportableInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\RegistrationFilter;
use App\Mapper\LevelFilterMapper;
use App\Mapper\User\UserListExportMapper;
use App\Repository\MemberRepository;
use App\Service\SeasonService;
use App\State\Registration\Trait\RegistrationListDataTrait;

class RegistrationListExportProvider implements StreamListExportableInterface
{
    use FilterHydratorTrait;
    use RegistrationListDataTrait;

    public function __construct(
        private LevelFilterMapper $levelFilterMapper,
        private UserListExportMapper $exportMapper,
        private MemberRepository $memberRepository,
        private SeasonService $seasonService,
    ) {
    }

    public function streamExportContent(AbstractFilter $filter, ?HandlerContext $context = null): void
    {
        /** @var RegistrationFilter $filter */

        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        $this->exportMapper->streamToCsv($entities);
    }

    public function filename(HandlerContext|null $context = null): string
    {
        return  'export_assurances.csv';
    }
}
