<?php

declare(strict_types=1);

namespace App\State\ClusterSkill\Provider;

use App\Core\Contract\Provider\ComponentProviderInterface;
use App\Core\Contract\View\ComponentViewInterface;
use App\Core\Dto\HandlerContext;
use App\Entity\Cluster;
use App\Mapper\ClusterSkill\ClusterSkillsReadMapper;
use App\Repository\MemberRepository;

/**
 * @implements ComponentProviderInterface<Cluster>
 */
class ClusterSkillReadProvider implements ComponentProviderInterface
{
    public function __construct(
        private ClusterSkillsReadMapper $clusterSkillsReadMapper,
        private MemberRepository $memberRepository,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): ComponentViewInterface
    {
        $participants = $this->memberRepository->findEvaluablesByCluster($data);

        return $this->clusterSkillsReadMapper->mapToView($data, $participants);
    }
}
