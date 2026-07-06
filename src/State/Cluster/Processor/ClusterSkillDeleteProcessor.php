<?php

declare(strict_types=1);

namespace App\State\Cluster\Processor;

use App\Dto\Payload\ClusterSkillDto;
use App\Dto\State\HtmlProcessorResult;
use App\State\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ClusterSkillDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        /** @var ClusterSkillDto $entity */
        $cluster = $entity->cluster;
        $skill = $entity->skill;

        $cluster->removeSkill($skill);
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            messageKey: 'cluster_skill.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
