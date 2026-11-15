<?php

declare(strict_types=1);

namespace App\State\User\Provider;

use App\Core\Contract\Provider\ListJsonProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\UserFilter;
use App\Mapper\EmailClipboardMapper;
use App\Mapper\LevelFilterMapper;

;
use App\Repository\MemberRepository;
use App\State\User\Trait\UserListDataTrait;

/**
 * @implements ListJsonProviderInterface<UserFilter>
 */
class UserListEmailProvider implements ListJsonProviderInterface
{
    use FilterHydratorTrait;
    use UserListDataTrait;

    public function __construct(
        private MemberRepository $memberRepository,
        private LevelFilterMapper $levelFilterMapper,
        private EmailClipboardMapper $emailClipboardMapper,
    ) {
    }

    public function getJson(AbstractFilter $filter, ?HandlerContext $context = null): array
    {
        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        return ['emails' => $this->emailClipboardMapper->mapToEmailCsvString($entities)];
    }
}
