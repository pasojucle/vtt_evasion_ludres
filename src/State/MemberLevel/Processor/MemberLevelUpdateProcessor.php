<?php

declare(strict_types=1);

namespace App\State\MemberLevel\Processor;

use App\Dto\State\TurboStreamProcessorResult;
use App\Entity\Member;
use App\State\Interface\FormTurboStreamProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements FormTurboStreamProcessorInterface<Member>
 */
class MemberLevelUpdateProcessor implements FormTurboStreamProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): TurboStreamProcessorResult
    {
        $this->entityManager->flush();
    
        return new TurboStreamProcessorResult('member_level/admin/update.lazy.html.twig');
    }
}
