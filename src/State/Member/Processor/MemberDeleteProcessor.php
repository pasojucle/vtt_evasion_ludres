<?php

declare(strict_types=1);

namespace App\State\Member\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\Member\DeleteMember;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class MemberDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private DeleteMember $deleteMember,
    ) {
    }


    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $member = $payload->data;

        ($this->deleteMember)($member);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'member.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
