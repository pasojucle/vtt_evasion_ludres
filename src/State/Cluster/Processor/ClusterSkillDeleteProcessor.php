<?php

declare(strict_types=1);

namespace App\State\Cluster\Processor;

use App\Dto\ClusterSkillDto;
use App\Dto\State\ProcessorResult;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ClusterSkillDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        /** @var ClusterSkillDto $entity */
        $cluster = $entity->cluster;
        $skill = $entity->skill;

        $cluster->removeSkill($skill);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            messageKey: 'cluster_skill.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
