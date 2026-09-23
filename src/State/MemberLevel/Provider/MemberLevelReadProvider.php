<?php

declare(strict_types=1);

namespace App\State\MemberLevel\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\MemberLevel\MemberLevelView;
use App\Dto\View\SheetView;
use App\Entity\Member;
use App\Mapper\MemberLevel\MemberLevelReadMapper;

/**
 * @implements FormComponentProviderInterface<Member>
 */
class MemberLevelReadProvider implements FormComponentProviderInterface, TurboStreamProviderInterface
{
    public function __construct(
        private MemberLevelReadMapper $memberLevelReadMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        return new SheetView(
            title: 'Modifier',
            description: 'Modifier le niveau de l\'adhérent',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $data): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
            ],
        ];
    }

    public function getStreamView(object $entity, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): MemberLevelView
    {
        return $this->memberLevelReadMapper->mapToView($entity);
    }
}
