<?php

declare(strict_types=1);

namespace App\State\ClusterSkill\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\Cluster\DeleteClusterSkill;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class ClusterSkillDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private DeleteClusterSkill $deleteClusterSkill,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $cluster = $context->parent;
        $skill = $payload->data;

        ($this->deleteClusterSkill)($cluster, $skill);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'cluster_skill.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
