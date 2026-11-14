<?php

declare(strict_types=1);

namespace App\State\Registration\Provider;

use App\Core\Contract\Provider\ListJsonProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\RegistrationFilter;
use App\Mapper\EmailClipboardMapper;
use App\Mapper\LevelFilterMapper;
use App\Repository\MemberRepository;
use App\Service\SeasonService;
use App\State\Registration\Trait\RegistrationListDataTrait;

/**
 * @implements ListJsonProviderInterface<RegistrationFilter>
 */
class RegistrationListEmailProvider implements ListJsonProviderInterface
{
    use FilterHydratorTrait;
    use RegistrationListDataTrait;

    public function __construct(
        private LevelFilterMapper $levelFilterMapper,
        private EmailClipboardMapper $emailClipboardMapper,
        private MemberRepository $memberRepository,
        private SeasonService $seasonService,
    ) {
    }

    public function getJson(AbstractFilter $filter, HandlerContext|null $context = null): array
    {
        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        return ['emails' => $this->emailClipboardMapper->mapToEmailCsvString($entities)];
    }
}
