<?php

declare(strict_types=1);

namespace App\State\User\Provider;

use App\Core\Contract\Provider\StreamListExportableInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\UserFilter;
use App\Mapper\LevelFilterMapper;
use App\Mapper\User\UserListExportMapper;
use App\Repository\MemberRepository;
use App\State\User\Trait\UserListDataTrait;

/**
 * @implements StreamListExportableInterface<UserFilter>
 */
class UserListExportProvider implements StreamListExportableInterface
{
    use FilterHydratorTrait;
    use UserListDataTrait;

    public function __construct(
        private MemberRepository $memberRepository,
        private LevelFilterMapper $levelFilterMapper,
        private UserListExportMapper $exportMapper,
    ) {
    }

    public function streamExportContent(AbstractFilter $filter, ?HandlerContext $context = null): void
    {
        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        $this->exportMapper->streamToCsv($entities);
    }

    public function basename(?HandlerContext $context = null): string
    {
        return 'export_adherents.csv';
    }
}
