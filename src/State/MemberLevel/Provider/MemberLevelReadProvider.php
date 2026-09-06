<?php

declare(strict_types=1);

namespace App\State\MemberLevel\Provider;

use App\Dto\State\TurboStreamContext;
use App\Dto\View\MemberLevel\MemberLevelView;
use App\Dto\View\SheetView;
use App\Entity\Member;
use App\Mapper\MemberLevel\MemberLevelReadMapper;
use App\State\Interface\TurboStreamProviderInterface;

/**
 * @implements TurboStreamProviderInterface<Member>
 */
class MemberLevelReadProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private MemberLevelReadMapper $memberLevelReadMapper,
    ) {
    }

    public function getFormView(object $entity, ?string $fallback = null): SheetView
    {
        return new SheetView(
            title: 'Modifier',
            description: 'Modifier le niveau de l\'adhérent',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $entity): array
    {
        return [            
            'attr' => [
                'data-action'=> 'turbo:submit-end->sheet#handleFormSubmit',
            ],
        ];
    }

    public function getStreamView(object $entity, ?TurboStreamContext $context = null): MemberLevelView
    {
        return $this->memberLevelReadMapper->mapToView($entity);
    }
}
