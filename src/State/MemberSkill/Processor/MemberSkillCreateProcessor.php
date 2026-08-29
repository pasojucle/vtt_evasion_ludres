<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Processor;

use App\Dto\Payload\MemberSkillCreatePayload;
use App\Dto\State\TurboStreamProcessorResult;
use App\State\Interface\FormTurboStreamProcessorInterface;
use App\UseCase\v2\MemberSkill\CreateMemberSkill;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements FormTurboStreamProcessorInterface<MemberSkillCreatePayload>
 */
class MemberSkillCreateProcessor implements FormTurboStreamProcessorInterface
{
    public function __construct(
        private CreateMemberSkill $createMemberSkill,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(object $payload, ?array $uploadFiles, ?string $targetUrl = null): TurboStreamProcessorResult
    {
        $newEntity = ($this->createMemberSkill)(
            member: $payload->member,
            skill: $payload->skill,
        );

        $this->entityManager->persist($newEntity);
        $this->entityManager->flush();

        return new TurboStreamProcessorResult(true);
    }
}
