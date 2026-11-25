<?php

declare(strict_types=1);

namespace App\State\SurveyResponse\Provider;

use App\Core\Contract\Provider\JsonProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Mapper\EmailClipboardMapper;
use App\Repository\MemberRepository;

class SurveyResponseEmailProvider implements JsonProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private MemberRepository $memberRepository,
        private EmailClipboardMapper $emailClipboardMapper,
    ) {
    }

    public function getJson(?AbstractFilter $filter = null, ?HandlerContext $context = null): array
    {
        $members = $this->memberRepository->findRespondantsBySurvey($context->parent);
        
        return ['emails' => $this->emailClipboardMapper->mapToEmailCsvString($members)];
    }
}
