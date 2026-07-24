<?php

declare(strict_types=1);

namespace App\State\Cluster\Processor;

use App\Dto\Payload\ClusterSkillDto;
use App\Dto\State\RedirectProcessorResult;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ClusterSkillDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        /** @var ClusterSkillDto $entity */
        $cluster = $entity->cluster;
        $skill = $entity->skill;

        $cluster->removeSkill($skill);
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'cluster_skill.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
