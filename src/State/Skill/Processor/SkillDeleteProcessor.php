<?php

declare(strict_types=1);

namespace App\State\Skill\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Skill;
use App\Service\FilterDecoderService;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SkillDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FilterDecoderService $filterDecoder,
    ) {
    }

    public function process(object $entity, ?string $filter): ProcessorResult
    {
        /**  @var skill $entity */
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            messageKey: 'level.flash.success.delete',
            targetRoute: 'admin_level_list',
            routeParams: $this->filterDecoder->decode($filter),
            flashType: 'success',
        );
    }
}
