<?php

declare(strict_types=1);

namespace App\State\MemberStatus\Provider;

use App\Dto\View\MemberStatus\MemberStatusSheetView;
use App\Dto\View\MemberStatus\MemberStatusView;
use App\Entity\Member;
use App\Mapper\MemberStatus\MemberStatusReadMapper;
use App\State\Interface\TurboStreamProviderInterface;

/**
 * @implements TurboStreamProviderInterface<Member>
 */
class MemberStatusReadProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private MemberStatusReadMapper $memberClubReadMapper,
    ) {
    }

    public function getFormView(object $entity, ?string $fallback = null): MemberStatusSheetView
    {
        return new MemberStatusSheetView(
            title: 'Modifier',
            description: 'Modifier le statut de l\'adhérent dans le club ',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $entity): array
    {
        return [];
    }

    public function getStreamView(object $entity, array $context = []): MemberStatusView
    {
        return $this->memberClubReadMapper->mapToView($entity);
    }
}
