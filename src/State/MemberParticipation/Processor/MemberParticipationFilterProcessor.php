<?php

declare(strict_types=1);

namespace App\State\MemberParticipation\Processor;

use App\Dto\State\TurboStreamProcessorResult;
use App\Entity\Member;
use App\State\Interface\FormTurboStreamProcessorInterface;


/**
 * @implements FormTurboStreamProcessorInterface<Member>
 */
class MemberParticipationFilterProcessor implements FormTurboStreamProcessorInterface
{
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): TurboStreamProcessorResult
    {
    
        return new TurboStreamProcessorResult('member_participation/admin/update.lazy.html.twig');
    }
}
